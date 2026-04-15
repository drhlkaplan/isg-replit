<?php
namespace ISG;

class SCORMManager {
    private DB $db;

    public function __construct() {
        $this->db = DB::getInstance();
    }

    public function extractPackage(string $zipPath, int $courseId): array {
        $targetDir = SCORM_DIR . 'course_' . $courseId . '/';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            throw new \RuntimeException('SCORM paketi açılamadı.');
        }
        $zip->extractTo($targetDir);
        $zip->close();

        if (!file_exists($targetDir . 'imsmanifest.xml')) {
            $found = $this->findManifestDir($targetDir);
            if ($found) {
                $this->flattenIntoParent($found, $targetDir);
            }
        }

        return $this->parseManifest($targetDir, $courseId);
    }

    private function findManifestDir(string $baseDir): ?string {
        foreach (scandir($baseDir) as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $fullPath = $baseDir . $entry;
            if (is_dir($fullPath) && file_exists($fullPath . '/imsmanifest.xml')) {
                return $fullPath . '/';
            }
        }
        foreach (scandir($baseDir) as $entry) {
            if ($entry === '.' || $entry === '..') continue;
            $fullPath = $baseDir . $entry;
            if (is_dir($fullPath)) {
                $deep = $this->findManifestDir($fullPath . '/');
                if ($deep) return $deep;
            }
        }
        return null;
    }

    private function flattenIntoParent(string $fromDir, string $toDir): void {
        foreach (scandir($fromDir) as $item) {
            if ($item === '.' || $item === '..') continue;
            $src = $fromDir . $item;
            $dst = $toDir . $item;
            if (file_exists($dst)) {
                if (is_dir($dst)) {
                    $this->flattenIntoParent($src . '/', $dst . '/');
                    @rmdir($src);
                } else {
                    unlink($dst);
                    rename($src, $dst);
                }
            } else {
                rename($src, $dst);
            }
        }
        @rmdir($fromDir);
    }

    public function parseManifest(string $packageDir, int $courseId): array {
        $manifestFile = $packageDir . 'imsmanifest.xml';
        if (!file_exists($manifestFile)) {
            throw new \RuntimeException('imsmanifest.xml bulunamadı.');
        }

        $xml = simplexml_load_file($manifestFile);
        if (!$xml) {
            throw new \RuntimeException('imsmanifest.xml ayrıştırılamadı.');
        }

        $namespaces = $xml->getNamespaces(true);
        $ns = isset($namespaces['']) ? '' : ($namespaces['imscp'] ?? '');

        $scormVersion = $this->detectVersion($xml, $namespaces);
        $title = $this->extractTitle($xml, $namespaces);
        $launchUrl = $this->extractLaunchUrl($xml, $namespaces, $packageDir);

        return [
            'course_id'    => $courseId,
            'scorm_version' => $scormVersion,
            'package_path' => 'course_' . $courseId . '/',
            'launch_url'   => $launchUrl,
            'title'        => $title,
            'manifest_data' => json_encode([
                'version' => $scormVersion,
                'title'   => $title,
                'launch'  => $launchUrl,
            ]),
        ];
    }

    private function detectVersion(\SimpleXMLElement $xml, array $namespaces): string {
        $xmlStr = $xml->asXML();
        if (str_contains($xmlStr, 'CAM.1.3') || str_contains($xmlStr, 'adlcp_rootv1p2') ||
            str_contains($xmlStr, 'scorm12') || str_contains($xmlStr, '1.2')) {
            return '1.2';
        }
        if (str_contains($xmlStr, '2004') || str_contains($xmlStr, 'adlcp_v1p3')) {
            return '2004';
        }
        return '1.2';
    }

    private function extractTitle(\SimpleXMLElement $xml, array $namespaces): string {
        $metadata = $xml->metadata ?? null;
        if ($metadata) {
            foreach ($metadata->children() as $child) {
                if ($child->getName() === 'schema') {
                    continue;
                }
            }
        }

        $orgs = $xml->organizations ?? null;
        if ($orgs) {
            foreach ($orgs->children() as $org) {
                $t = (string)($org->title ?? '');
                if ($t) return $t;
                foreach ($org->children() as $item) {
                    $t = (string)($item->title ?? '');
                    if ($t) return $t;
                }
            }
        }

        return 'SCORM Kursu';
    }

    private function extractLaunchUrl(\SimpleXMLElement $xml, array $namespaces, string $packageDir): string {
        $resources = $xml->resources ?? null;
        if (!$resources) {
            throw new \RuntimeException('Manifest\'te resources bulunamadı.');
        }

        $launchHref = '';
        $orgs = $xml->organizations ?? null;
        $defaultOrg = (string)($orgs['default'] ?? '');

        if ($orgs && $defaultOrg) {
            foreach ($orgs->children() as $org) {
                if ((string)($org['identifier'] ?? '') === $defaultOrg) {
                    foreach ($org->children() as $item) {
                        $resourceId = (string)($item['identifierref'] ?? '');
                        if ($resourceId) {
                            foreach ($resources->children() as $res) {
                                if ((string)($res['identifier'] ?? '') === $resourceId) {
                                    $launchHref = (string)($res['href'] ?? '');
                                    break 3;
                                }
                            }
                        }
                    }
                }
            }
        }

        if (!$launchHref) {
            foreach ($resources->children() as $res) {
                $type = (string)($res['type'] ?? '');
                if (str_contains($type, 'sco') || str_contains($type, 'SCO')) {
                    $launchHref = (string)($res['href'] ?? '');
                    if ($launchHref) break;
                }
            }
        }

        if (!$launchHref) {
            foreach ($resources->children() as $res) {
                $href = (string)($res['href'] ?? '');
                if ($href && (str_ends_with($href, '.html') || str_ends_with($href, '.htm'))) {
                    $launchHref = $href;
                    break;
                }
            }
        }

        if (!$launchHref) {
            throw new \RuntimeException('SCORM başlatma dosyası bulunamadı.');
        }

        $html5Candidate = preg_replace('/\.html?$/i', '_html5.html', $launchHref);
        if ($html5Candidate !== $launchHref && file_exists($packageDir . $html5Candidate)) {
            return $html5Candidate;
        }

        if (file_exists($packageDir . 'index_lms_html5.html')) {
            return 'index_lms_html5.html';
        }
        if (file_exists($packageDir . 'story_html5.html')) {
            return 'story_html5.html';
        }

        return $launchHref;
    }

    public function getTrackingData(int $userId, int $courseId): array {
        $row = $this->db->fetch(
            'SELECT * FROM scorm_tracking WHERE user_id = ? AND course_id = ?',
            [$userId, $courseId]
        );
        return $row ?? [
            'lesson_status'     => 'not attempted',
            'completion_status' => 'incomplete',
            'score_raw'         => null,
            'total_time'        => '0000:00:00.00',
            'entry'             => 'ab-initio',
            'suspend_data'      => '',
            'location'          => '',
        ];
    }

    public function saveTrackingData(int $userId, int $courseId, array $data): void {
        $existing = $this->db->fetch(
            'SELECT id FROM scorm_tracking WHERE user_id = ? AND course_id = ?',
            [$userId, $courseId]
        );

        $cleanData = [];
        $allowed = ['lesson_status','completion_status','success_status','score_raw',
                    'score_min','score_max','score_scaled','total_time','session_time',
                    'entry','suspend_data','location','objectives','interactions',
                    'learner_preferences',
                    'tab_switch_count','fast_forward_count','low_quality_flag'];

        foreach ($allowed as $field) {
            if (isset($data[$field])) {
                $cleanData[$field] = $data[$field];
            }
        }

        if ($this->isTotalTimeValid($data)) {
            $cleanData['total_time'] = $this->addTimes(
                $existing ? ($this->db->fetch('SELECT total_time FROM scorm_tracking WHERE user_id = ? AND course_id = ?', [$userId, $courseId])['total_time'] ?? '0000:00:00.00') : '0000:00:00.00',
                $data['session_time'] ?? '0000:00:00.00'
            );
        }

        if ($existing) {
            $this->db->update('scorm_tracking', $cleanData, 'user_id = ? AND course_id = ?', [$userId, $courseId]);
        } else {
            $cleanData['user_id'] = $userId;
            $cleanData['course_id'] = $courseId;
            $this->db->insert('scorm_tracking', $cleanData);
        }

        $this->updateEnrollmentProgress($userId, $courseId, $data);
    }

    private function updateEnrollmentProgress(int $userId, int $courseId, array $data): void {
        /* Compliance-only commits (tab_switch / fast_forward) carry no SCORM status fields.
         * Attempting to derive enrollment progress from an empty status would reset a
         * previously-completed enrollment to in_progress/0%. Guard: skip unless a
         * meaningful SCORM completion signal is present. */
        $statusRaw = $data['lesson_status'] ?? $data['completion_status'] ?? '';
        if ($statusRaw === '') {
            return; // nothing to derive — do not touch enrollments
        }

        $status = strtolower($statusRaw);
        $passed = in_array($status, ['passed', 'completed']);
        $failed = $status === 'failed';

        /* Also guard: never downgrade an enrollment that is already completed/failed */
        $current = $this->db->fetch(
            'SELECT status FROM enrollments WHERE user_id = ? AND course_id = ?',
            [$userId, $courseId]
        );
        if ($current && in_array($current['status'], ['completed', 'failed'])) {
            /* Only allow re-write when the new signal is also a terminal state */
            if (!$passed && !$failed) {
                return;
            }
        }

        $progress = 0;
        if ($passed) $progress = 100;
        elseif (in_array($status, ['incomplete', 'in progress', 'browsed'])) $progress = 50;

        $enrollStatus = 'in_progress';
        if ($passed) $enrollStatus = 'completed';
        elseif ($failed) $enrollStatus = 'failed';

        $this->db->query(
            'UPDATE enrollments SET status = ?, progress_percent = ?, last_activity = NOW()' .
            ($passed ? ', completed_at = NOW()' : '') .
            ' WHERE user_id = ? AND course_id = ?',
            array_merge([$enrollStatus, $progress], [$userId, $courseId])
        );

        if ($passed) {
            (new \ISG\PackageEnroller($this->db))->onModuleCompleted($userId, $courseId);
        }
    }

    private function addTimes(string $time1, string $time2): string {
        $t1 = $this->parseTime($time1);
        $t2 = $this->parseTime($time2);
        $total_seconds = $t1 + $t2;
        $h = floor($total_seconds / 3600);
        $m = floor(($total_seconds % 3600) / 60);
        $s = $total_seconds % 60;
        return sprintf('%04d:%02d:%05.2f', $h, $m, $s);
    }

    private function parseTime(string $time): int {
        $parts = explode(':', str_replace(',', '.', $time));
        $h = (int)($parts[0] ?? 0);
        $m = (int)($parts[1] ?? 0);
        $s = (int)floor((float)($parts[2] ?? 0));
        return $h * 3600 + $m * 60 + $s;
    }

    private function isTotalTimeValid(array $data): bool {
        return !empty($data['session_time']) && $data['session_time'] !== '0000:00:00.00';
    }
}
