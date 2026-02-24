<?php

declare(strict_types=1);

namespace HyperfLog;

use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

#[Controller(prefix: '/log_view')]
class LogViewerController
{
    protected string $logPath;
    protected string $viewPath;
    protected string $cachePath;
    protected int $limit;
    protected int $cacheExpiry;

    #[Inject]
    protected LogTypeManager $logTypeManager;

    public function __construct(
        protected RequestInterface $request,
        protected ResponseInterface $response,
        protected ConfigInterface $config
    ) {
        $this->logPath = (string) $this->config->get('log_viewer.log_path', BASE_PATH . '/runtime/logs');
        $this->viewPath = dirname(__DIR__) . '/resources';
        $this->cachePath = (string) $this->config->get('log_viewer.cache_path', BASE_PATH . '/runtime/cache/log_viewer');
        $this->limit = (int) $this->config->get('log_viewer.per_page', 50);
        $this->cacheExpiry = (int) $this->config->get('log_viewer.cache_expiry', 60);

        if (!is_dir($this->cachePath)) {
            @mkdir($this->cachePath, 0755, true);
        }
    }

    /**
     * 获取当前路由基础路径
     * 优先使用配置值，支持反向代理场景；其次尝试从常见代理头中获取原始路径
     */
    protected function getBasePath(): string
    {
        // 1. 优先使用配置
        $configured = (string) $this->config->get('log_viewer.base_path', '');
        if ($configured !== '') {
            return rtrim($configured, '/');
        }

        // 2. 尝试从代理头获取原始完整路径
        $originalUri = $this->request->getHeaderLine('X-Original-URI')
            ?: $this->request->getHeaderLine('X-Forwarded-Prefix');
        if (!empty($originalUri)) {
            return (string) preg_replace('/\/log_view.*$/', '/log_view', $originalUri);
        }

        // 3. 回退：从请求 URI 推断
        $requestUri = $this->request->getUri()->getPath();
        return (string) preg_replace('/\/log_view.*$/', '/log_view', $requestUri);
    }

    // ==================== 日志类型配置管理 ====================

    /**
     * 日志类型配置管理页面
     */
    #[GetMapping(path: 'config')]
    public function logTypeConfig(): PsrResponseInterface
    {
        $logTypes = $this->logTypeManager->getAll();
        $_basePath = $this->getBasePath();

        $availableChannels = $this->logTypeManager->scanAvailableChannels();

        ob_start();
        include $this->viewPath . '/views/log-type-config.php';
        $html = ob_get_clean();

        return $this->response->html($html);
    }

    /**
     * 保存日志类型（AJAX）
     */
    #[PostMapping(path: 'config/save')]
    public function saveLogType(): PsrResponseInterface
    {
        $data = json_decode((string) $this->request->getBody(), true);
        if (empty($data)) {
            return $this->response->json(['success' => false, 'message' => '无效的请求数据']);
        }

        try {
            $newType = $this->logTypeManager->save($data);
            return $this->response->json(['success' => true, 'message' => '保存成功', 'data' => $newType]);
        } catch (\InvalidArgumentException $e) {
            return $this->response->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * 导入日志类型（AJAX）
     */
    #[PostMapping(path: 'config/import')]
    public function importLogTypes(): PsrResponseInterface
    {
        $body = (string) $this->request->getBody();
        $data = json_decode($body, true);

        if ($data === null) {
            return $this->response->json(['success' => false, 'message' => '无效的 JSON 格式']);
        }

        // 支持传入单个对象或数组
        if (isset($data['id']) || isset($data['name'])) {
            $data = [$data];
        }

        if (empty($data) || !is_array($data)) {
            return $this->response->json(['success' => false, 'message' => '无效的数据格式，需要 JSON 数组']);
        }

        try {
            $result = $this->logTypeManager->import($data);
            $msg = sprintf('导入完成：新增 %d 个，更新 %d 个', $result['imported'], $result['updated']);
            if ($result['skipped'] > 0) {
                $msg .= sprintf('，跳过 %d 个（缺少必填字段）', $result['skipped']);
            }
            return $this->response->json(['success' => true, 'message' => $msg, 'data' => $result]);
        } catch (\Exception $e) {
            return $this->response->json(['success' => false, 'message' => '导入失败: ' . $e->getMessage()]);
        }
    }

    /**
     * 导出所有日志类型（JSON 文件下载）
     */
    #[GetMapping(path: 'config/export')]
    public function exportLogTypes(): PsrResponseInterface
    {
        $logTypes = $this->logTypeManager->getAll();

        return $this->response->json($logTypes)
            ->withHeader('Content-Disposition', 'attachment; filename="log-viewer-types-' . date('Ymd') . '.json"');
    }

    /**
     * 删除日志类型（AJAX）
     */
    #[PostMapping(path: 'config/delete')]
    public function deleteLogType(): PsrResponseInterface
    {
        $data = json_decode((string) $this->request->getBody(), true);
        $id = $data['id'] ?? '';

        if (empty($id)) {
            return $this->response->json(['success' => false, 'message' => '缺少ID']);
        }

        try {
            $this->logTypeManager->delete($id);
            return $this->response->json(['success' => true, 'message' => '删除成功']);
        } catch (\InvalidArgumentException $e) {
            return $this->response->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ==================== 主入口 ====================

    #[GetMapping(path: '')]
    public function index(): PsrResponseInterface
    {
        $mode = $this->request->input('mode', '');

        // 所有日志类型统一走通用处理
        if (!empty($mode)) {
            $logType = $this->logTypeManager->findByMode($mode);
            if ($logType) {
                return $this->customLogType($logType);
            }
        }

        // 获取所有日志类型（用于 Tab 展示）
        $logTypes = $this->logTypeManager->getAll();

        // 获取参数
        $currentFile = $this->request->input('file', '');
        $search = $this->request->input('search', '');
        $currentPage = (int) $this->request->input('page', 1);

        // 扫描日志文件
        $allLogs = $this->scanLogFiles($this->logPath);
        $filesByDate = $this->groupFilesByDate($allLogs);

        // 加载日志内容
        $logs = [];
        $total = 0;
        $totalPage = 0;

        if (!empty($currentFile)) {
            $result = $this->loadLogContent($currentFile, $search, $currentPage);
            $logs = $result['logs'];
            $total = $result['total'];
            $totalPage = $result['totalPage'];
        }

        $_basePath = $this->getBasePath();

        ob_start();
        include $this->viewPath . '/views/index.php';
        $html = ob_get_clean();

        return $this->response->html($html);
    }

    // ==================== 通用日志类型处理 ====================

    /**
     * 处理所有日志类型请求
     */
    protected function customLogType(array $logType): PsrResponseInterface
    {
        $date = $this->request->input('date', date('Y-m-d'));
        $search = $this->request->input('search', '');
        $aggregateId = $this->request->input('agg_id', '');
        $page = (int) $this->request->input('page', 1);

        $isAjax = $this->request->input('ajax') === '1'
            || $this->request->hasHeader('X-Requested-With');

        $logTypes = $this->logTypeManager->getAll();
        $itemLogs = [];
        $currentItem = null;
        $items = [];
        $total = 0;
        $totalPage = 0;

        if (!empty($aggregateId)) {
            $itemLogs = $this->loadCustomLogsFast($aggregateId, $date, $logType);
            $currentItem = $this->buildItemFromLogs($aggregateId, $itemLogs, $logType);
        } else {
            $items = $this->loadCustomItemsWithCache($date, $logType);

            if (!empty($search)) {
                $items = array_filter($items, function ($item) use ($search) {
                    return stripos($item['agg_id'], $search) !== false;
                });
            }

            uasort($items, fn($a, $b) => strcmp($b['start_time'], $a['start_time']));

            $total = count($items);
            $totalPage = (int) ceil($total / $this->limit);
            $items = array_slice($items, ($page - 1) * $this->limit, $this->limit, true);
        }

        if ($isAjax && empty($aggregateId)) {
            return $this->response->json([
                'items' => array_values($items),
                'total' => $total,
                'totalPage' => $totalPage,
                'page' => $page,
                'date' => $date,
            ]);
        }

        $_basePath = $this->getBasePath();

        ob_start();
        include $this->viewPath . '/views/aggregate-log.php';
        $html = ob_get_clean();

        return $this->response->html($html);
    }

    // ==================== 日志内容加载 ====================

    protected function loadLogContent(string $file, string $search, int $page): array
    {
        // 安全检查：防止路径遍历攻击
        $file = basename($file);
        $filePath = $this->logPath . '/' . $file;
        if (!file_exists($filePath) || !is_file($filePath)) {
            return ['logs' => [], 'total' => 0, 'totalPage' => 0];
        }

        $content = file_get_contents($filePath);
        $lines = array_filter(explode("\n", $content));
        $contentArr = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $json = json_decode($line, true);
            if ($json !== null) {
                $entry = [
                    'time' => $json['datetime'] ?? '',
                    'level' => strtolower($json['level_name'] ?? 'info'),
                    'channel' => $json['channel'] ?? '',
                    'message' => $json['message'] ?? '',
                    'context' => isset($json['context']) ? json_encode($json['context'], JSON_UNESCAPED_UNICODE) : '',
                ];
            } else {
                preg_match('/(\d{4}-\d{2}-\d{2}[T\s]\d{2}:\d{2}:\d{2})/', $line, $times);
                $entry = [
                    'time' => $times[0] ?? '',
                    'level' => $this->detectLevel($line),
                    'channel' => '',
                    'message' => $line,
                    'context' => '',
                ];
            }

            if (!empty($search)) {
                $searchLower = strtolower($search);
                $lineContent = strtolower($entry['message'] . $entry['context']);
                if (strpos($lineContent, $searchLower) === false) continue;
            }

            $contentArr[] = $entry;
        }

        usort($contentArr, fn($a, $b) => strcmp($b['time'], $a['time']));

        $total = count($contentArr);
        $totalPage = (int) ceil($total / $this->limit);
        $offset = ($page - 1) * $this->limit;
        $logs = array_slice($contentArr, $offset, $this->limit);

        return ['logs' => $logs, 'total' => $total, 'totalPage' => $totalPage];
    }

    protected function detectLevel(string $line): string
    {
        $line = strtolower($line);
        if (strpos($line, '[error]') !== false || strpos($line, '"level_name":"error"') !== false) return 'error';
        if (strpos($line, '[warning]') !== false || strpos($line, '"level_name":"warning"') !== false) return 'warning';
        if (strpos($line, '[debug]') !== false || strpos($line, '"level_name":"debug"') !== false) return 'debug';
        return 'info';
    }

    // ==================== 聚合日志加载 ====================

    protected function loadCustomLogs(string $date, array $logType): array
    {
        $allLogs = [];
        $files = $this->scanLogFiles($this->logPath);
        $filePattern = $logType['file_pattern'] ?? $logType['log_channel'];
        $grepPattern = $logType['grep_pattern'] ?? '';
        $aggregateField = $logType['aggregate_field'] ?? 'task_id';
        $aggregatePattern = $logType['aggregate_pattern'] ?? '';
        $logChannel = $logType['log_channel'] ?? '';

        foreach ($files as $file) {
            if (strpos($file, $date) === false) continue;
            if (!empty($filePattern) && strpos($file, $filePattern) === false) continue;

            $filePath = $this->logPath . '/' . $file;
            if (!file_exists($filePath)) continue;

            $lines = !empty($grepPattern)
                ? $this->grepLines($filePath, $grepPattern)
                : file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $json = json_decode($line, true);
                if ($json === null) continue;

                $channel = (string) ($json['channel'] ?? '');
                if (!empty($logChannel) && $channel !== $logChannel) continue;

                $context = $json['context'] ?? [];
                $aggValue = '';
                if (isset($context[$aggregateField]) && is_string($context[$aggregateField])) {
                    $aggValue = $context[$aggregateField];
                } elseif (!empty($aggregatePattern)) {
                    $fullLine = json_encode($json, JSON_UNESCAPED_UNICODE);
                    if (preg_match('/(' . $aggregatePattern . ')/', $fullLine, $m)) {
                        $aggValue = $m[1];
                    }
                }

                if (empty($aggValue)) continue;

                $allLogs[] = [
                    'agg_id' => $aggValue,
                    'time' => (string) ($json['datetime'] ?? ''),
                    'level' => strtolower((string) ($json['level_name'] ?? 'info')),
                    'channel' => $channel,
                    'message' => (string) ($json['message'] ?? ''),
                    'stage' => $context['stage'] ?? '',
                ];
            }
        }

        return $allLogs;
    }

    protected function loadCustomLogsFast(string $aggId, string $date, array $logType): array
    {
        $itemLogs = [];
        $files = $this->scanLogFiles($this->logPath);
        $filePattern = $logType['file_pattern'] ?? $logType['log_channel'];
        $logChannel = $logType['log_channel'] ?? '';

        foreach ($files as $file) {
            if (strpos($file, $date) === false) continue;
            if (!empty($filePattern) && strpos($file, $filePattern) === false) continue;

            $filePath = $this->logPath . '/' . $file;
            if (!file_exists($filePath)) continue;

            $lines = $this->grepLines($filePath, $aggId);

            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;

                $json = json_decode($line, true);
                if ($json === null) continue;

                $channel = (string) ($json['channel'] ?? '');
                if (!empty($logChannel) && $channel !== $logChannel) continue;

                $fullLine = json_encode($json, JSON_UNESCAPED_UNICODE);
                if (strpos($fullLine, $aggId) === false) continue;

                $itemLogs[] = [
                    'agg_id' => $aggId,
                    'time' => (string) ($json['datetime'] ?? ''),
                    'level' => strtolower((string) ($json['level_name'] ?? 'info')),
                    'channel' => $channel,
                    'message' => (string) ($json['message'] ?? ''),
                    'context' => $json['context'] ?? [],
                ];
            }
        }

        usort($itemLogs, fn($a, $b) => strcmp($a['time'], $b['time']));
        return $itemLogs;
    }

    protected function loadCustomItemsWithCache(string $date, array $logType): array
    {
        $cacheFile = $this->cachePath . '/custom_' . $logType['mode'] . '_' . $date . '.json';

        if (file_exists($cacheFile)) {
            $cacheTime = filemtime($cacheFile);
            $isToday = ($date === date('Y-m-d'));
            $expiry = $isToday ? $this->cacheExpiry : 600;

            if ((time() - $cacheTime) < $expiry) {
                $cached = file_get_contents($cacheFile);
                $items = json_decode($cached, true);
                if ($items !== null) return $items;
            }
        }

        $allLogs = $this->loadCustomLogs($date, $logType);
        $items = $this->aggregateByField($allLogs, $logType);

        @file_put_contents($cacheFile, json_encode($items, JSON_UNESCAPED_UNICODE));

        return $items;
    }

    // ==================== 聚合与构建 ====================

    protected function aggregateByField(array $logs, array $logType): array
    {
        $items = [];
        $successStage = $logType['success_stage'] ?? '';

        foreach ($logs as $log) {
            $aggId = $log['agg_id'];
            if (empty($aggId)) continue;

            if (!isset($items[$aggId])) {
                $items[$aggId] = [
                    'agg_id' => $aggId,
                    'start_time' => $log['time'],
                    'end_time' => $log['time'],
                    'log_count' => 0,
                    'error_count' => 0,
                    'status' => 'processing',
                ];
            }

            $item = &$items[$aggId];

            if ($log['time'] < $item['start_time']) $item['start_time'] = $log['time'];
            if ($log['time'] > $item['end_time']) $item['end_time'] = $log['time'];

            $item['log_count']++;

            if ($log['level'] === 'error') {
                $item['error_count']++;
                $item['status'] = 'failed';
            }

            $stage = $log['stage'] ?? '';
            if (!empty($successStage) && $stage === $successStage && $item['status'] !== 'failed') {
                $item['status'] = 'success';
            }

            unset($item);
        }

        return $items;
    }

    protected function buildItemFromLogs(string $aggId, array $logs, array $logType): ?array
    {
        if (empty($logs)) return null;

        $successStage = $logType['success_stage'] ?? '';
        $item = [
            'agg_id' => $aggId,
            'start_time' => $logs[0]['time'] ?? '',
            'end_time' => end($logs)['time'] ?? '',
            'log_count' => count($logs),
            'error_count' => 0,
            'status' => 'processing',
        ];

        foreach ($logs as $log) {
            if ($log['level'] === 'error') {
                $item['error_count']++;
                $item['status'] = 'failed';
            }
            $stage = $log['context']['stage'] ?? '';
            if (!empty($successStage) && $stage === $successStage && $item['status'] !== 'failed') {
                $item['status'] = 'success';
            }
        }

        return $item;
    }

    // ==================== 工具方法 ====================

    protected function grepLines(string $filePath, string $pattern): array
    {
        $escapedPattern = escapeshellarg($pattern);
        $escapedPath = escapeshellarg($filePath);

        if (PHP_OS_FAMILY === 'Windows') {
            $cmd = "findstr /C:{$escapedPattern} {$escapedPath}";
        } else {
            // 使用 -F 固定字符串匹配，避免正则元字符误匹配
            $cmd = "grep -F {$escapedPattern} {$escapedPath}";
        }

        $output = [];
        exec($cmd, $output);

        return $output;
    }

    protected function scanLogFiles(string $dir): array
    {
        $files = [];
        if (!is_dir($dir)) return $files;

        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $dir . '/' . $item;
            if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'log') {
                $files[] = $item;
            }
        }

        usort($files, function ($a, $b) use ($dir) {
            return filemtime($dir . '/' . $b) - filemtime($dir . '/' . $a);
        });

        return $files;
    }

    protected function groupFilesByDate(array $files): array
    {
        $grouped = [];

        foreach ($files as $file) {
            if (preg_match('/(\d{4}-\d{2}-\d{2})/', $file, $matches)) {
                $date = $matches[1];
            } elseif (preg_match('/(\d{8})/', $file, $matches)) {
                $d = $matches[1];
                $date = substr($d, 0, 4) . '-' . substr($d, 4, 2) . '-' . substr($d, 6, 2);
            } else {
                $date = '其他';
            }

            if (!isset($grouped[$date])) {
                $grouped[$date] = [];
            }
            $grouped[$date][] = $file;
        }

        krsort($grouped);

        return $grouped;
    }

}
