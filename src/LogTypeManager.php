<?php

declare(strict_types=1);

namespace Giikin\HyperfLogViewer;

use Hyperf\Contract\ConfigInterface;

class LogTypeManager
{
    protected string $configPath;
    protected string $logPath;

    public function __construct(protected ConfigInterface $config)
    {
        $this->configPath = (string) $this->config->get('log_viewer.config_path', BASE_PATH . '/runtime/log-viewer-types.json');
        $this->logPath = (string) $this->config->get('log_viewer.log_path', BASE_PATH . '/runtime/logs');
    }

    /**
     * 获取所有日志类型配置
     */
    public function getAll(): array
    {
        if (!file_exists($this->configPath)) {
            return [];
        }
        $json = file_get_contents($this->configPath);
        return json_decode($json, true) ?: [];
    }

    /**
     * 保存所有日志类型配置
     */
    public function saveAll(array $types): void
    {
        $dir = dirname($this->configPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        file_put_contents($this->configPath, json_encode($types, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    /**
     * 按 mode 查找日志类型
     */
    public function findByMode(string $mode): ?array
    {
        foreach ($this->getAll() as $type) {
            if ($type['mode'] === $mode) {
                return $type;
            }
        }
        return null;
    }

    /**
     * 保存单个日志类型（新增或更新）
     */
    public function save(array $data): array
    {
        $types = $this->getAll();
        $id = $data['id'] ?? '';
        $isNew = empty($id);

        $name = trim($data['name'] ?? '');
        $mode = trim($data['mode'] ?? '');
        $logChannel = trim($data['log_channel'] ?? '');
        $aggregateField = trim($data['aggregate_field'] ?? '');

        if (empty($name) || empty($mode) || empty($logChannel) || empty($aggregateField)) {
            throw new \InvalidArgumentException('名称、模式标识、日志通道、聚合字段不能为空');
        }

        // 新增时自动处理 mode 冲突
        if ($isNew) {
            $existingModes = array_column($types, 'mode');
            $baseMode = $mode;
            $suffix = 2;
            while (in_array($mode, $existingModes)) {
                $mode = $baseMode . '-' . $suffix++;
            }
        } else {
            foreach ($types as $t) {
                if ($t['mode'] === $mode && $t['id'] !== $id) {
                    throw new \InvalidArgumentException('模式标识已存在');
                }
            }
        }

        // 解析 stages
        $stages = [];
        if (!empty($data['stages'])) {
            $stages = is_string($data['stages'])
                ? (json_decode($data['stages'], true) ?: [])
                : $data['stages'];
        }

        $newType = [
            'id' => $isNew ? $mode : $id,
            'name' => $name,
            'icon' => trim($data['icon'] ?? '📋'),
            'mode' => $mode,
            'log_channel' => $logChannel,
            'file_pattern' => trim($data['file_pattern'] ?? $logChannel),
            'aggregate_field' => $aggregateField,
            'aggregate_pattern' => trim($data['aggregate_pattern'] ?? ''),
            'grep_pattern' => trim($data['grep_pattern'] ?? ''),
            'stages' => $stages,
            'success_stage' => trim($data['success_stage'] ?? ''),
            'created_at' => $isNew ? date('Y-m-d H:i:s') : ($data['created_at'] ?? date('Y-m-d H:i:s')),
        ];

        if ($isNew) {
            $types[] = $newType;
        } else {
            foreach ($types as &$t) {
                if ($t['id'] === $id) {
                    $t = $newType;
                    break;
                }
            }
            unset($t);
        }

        $this->saveAll($types);

        return $newType;
    }

    /**
     * 删除日志类型
     */
    public function delete(string $id): void
    {
        $types = $this->getAll();
        $filtered = [];
        $found = false;

        foreach ($types as $t) {
            if ($t['id'] === $id) {
                $found = true;
                continue;
            }
            $filtered[] = $t;
        }

        if (!$found) {
            throw new \InvalidArgumentException('类型不存在');
        }

        $this->saveAll($filtered);
    }

    /**
     * 扫描可用的日志通道
     */
    public function scanAvailableChannels(): array
    {
        $logPath = $this->logPath;
        $channels = [];

        if (!is_dir($logPath)) {
            return $channels;
        }

        $items = scandir($logPath);
        $scannedCount = 0;

        // 按修改时间倒序
        $files = [];
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') continue;
            $path = $logPath . '/' . $item;
            if (is_file($path) && pathinfo($path, PATHINFO_EXTENSION) === 'log') {
                $files[$item] = filemtime($path);
            }
        }
        arsort($files);

        foreach (array_keys($files) as $file) {
            if ($scannedCount >= 5) break;

            $filePath = $logPath . '/' . $file;
            $handle = fopen($filePath, 'r');
            if (!$handle) continue;

            $lineCount = 0;
            while (($line = fgets($handle)) !== false && $lineCount < 100) {
                $line = trim($line);
                if (empty($line)) continue;

                $json = json_decode($line, true);
                if ($json && isset($json['channel'])) {
                    $ch = $json['channel'];
                    if (!in_array($ch, $channels)) {
                        $channels[] = $ch;
                    }
                }
                $lineCount++;
            }
            fclose($handle);
            $scannedCount++;
        }

        sort($channels);
        return $channels;
    }
}
