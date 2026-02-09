<!DOCTYPE html>
<html>
<head>
    <title>日志查看器</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <style><?php include __DIR__ . '/../css/style.css'; ?></style>
</head>
<body>

<!-- 页面头部 -->
<div class="page-header">
    <h1>📋 日志查看器</h1>
    <p>查看和搜索应用日志</p>
</div>

<!-- Tab 导航 -->
<div class="nav-tabs">
    <a class="nav-link active" href="?">📄 普通日志</a>
    <?php if (!empty($logTypes)): foreach ($logTypes as $lt): ?>
        <a class="nav-link" href="?mode=<?= htmlspecialchars($lt['mode']) ?>"><?= htmlspecialchars(($lt['icon'] ?? '📋') . ' ' . $lt['name']) ?></a>
    <?php endforeach; endif; ?>
    <a class="nav-link" href="<?= $_basePath ?>/config" style="margin-left: auto; background: #f0f0f0; color: #666; font-size: 13px;">⚙️ 类型管理</a>
</div>

<div class="main-container">
    <!-- 左侧文件列表 -->
    <div class="sidebar">
        <div class="sidebar-card">
            <div class="sidebar-header">📁 日志文件</div>
            <div class="file-list">
                <?php 
                $index = 0;
                $today = date('Y-m-d');
                foreach ($filesByDate as $date => $files): 
                    $isToday = ($date === $today);
                    $groupId = 'group_' . $index;
                    $fileCount = count($files);
                    $hasActiveFile = in_array($currentFile, $files);
                    $isExpanded = empty($currentFile) ? ($index === 0) : $hasActiveFile;
                    $displayStyle = $isExpanded ? '' : 'display:none;';
                    $toggleIcon = $isExpanded ? '▼' : '▶';
                ?>
                    <div class="date-group-header" onclick="toggleGroup('<?= $groupId ?>', this)">
                        <span>
                            <span class="toggle-icon"><?= $toggleIcon ?></span>
                            📅 <?= $date ?> 
                            <?php if ($isToday): ?><span class="today-badge">(今天)</span><?php endif; ?>
                        </span>
                        <span class="file-count"><?= $fileCount ?>个</span>
                    </div>
                    <div id="<?= $groupId ?>" style="<?= $displayStyle ?>">
                        <?php foreach ($files as $file): ?>
                            <a class="file-item <?= ($currentFile === $file) ? 'active' : '' ?>" 
                               href="?file=<?= urlencode($file) ?>">
                                <?= htmlspecialchars($file) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php 
                    $index++;
                endforeach; 
                ?>
                
                <?php if (empty($filesByDate)): ?>
                    <div style="padding: 20px; color: #999; text-align: center;">暂无日志文件</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- 右侧内容区 -->
    <div class="content-area">
        <div class="content-card">
            <div class="content-header">
                <div class="content-title">
                    <?php if (!empty($currentFile)): ?>
                        📄 <?= htmlspecialchars($currentFile) ?>
                        <span class="count">(<?= $total ?> 条记录)</span>
                    <?php else: ?>
                        请选择日志文件
                    <?php endif; ?>
                </div>
                <form class="search-box" method="get">
                    <?php if (!empty($currentFile)): ?>
                        <input type="hidden" name="file" value="<?= htmlspecialchars($currentFile) ?>">
                    <?php endif; ?>
                    <input type="text" name="search" placeholder="搜索日志内容..." value="<?= htmlspecialchars($search) ?>">
                    <button type="submit">搜索</button>
                </form>
            </div>
            
            <?php if (empty($logs)): ?>
                <div class="empty-state">
                    <div class="icon">📭</div>
                    <h3>暂无日志记录</h3>
                    <p>请从左侧选择日志文件查看</p>
                </div>
            <?php else: ?>
                <div class="table-container">
                    <table class="log-table">
                        <thead>
                            <tr>
                                <th class="time-col">时间</th>
                                <th class="level-col">级别</th>
                                <th class="channel-col">频道</th>
                                <th>内容</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($logs as $entry): 
                                $level = $entry['level'];
                                $levelClass = match ($level) {
                                    'error' => 'level-error',
                                    'warning' => 'level-warning',
                                    'info' => 'level-info',
                                    'debug' => 'level-debug',
                                    default => 'level-default',
                                };
                                $rowClass = match ($level) {
                                    'error' => 'row-error',
                                    'warning' => 'row-warning',
                                    default => '',
                                };
                            ?>
                                <tr class="<?= $rowClass ?>">
                                    <?php 
                                    $timeStr = $entry['time'];
                                    if (strpos($timeStr, 'T') !== false) {
                                        $timeStr = str_replace('T', ' ', substr($timeStr, 0, 19));
                                    }
                                    ?>
                                    <td class="time-col"><?= htmlspecialchars($timeStr) ?></td>
                                    <td class="level-col">
                                        <span class="level-badge <?= $levelClass ?>"><?= $level ?></span>
                                    </td>
                                    <td class="channel-col"><?= htmlspecialchars($entry['channel']) ?></td>
                                    <td class="content-col">
                                        <?= htmlspecialchars($entry['message']) ?>
                                        <?php if (!empty($entry['context']) && $entry['context'] !== '{}' && $entry['context'] !== '[]'): ?>
                                            <div class="context-text"><?= htmlspecialchars($entry['context']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if ($totalPage > 1): ?>
                    <?php 
                    $baseUrl = '?file=' . urlencode($currentFile);
                    if (!empty($search)) {
                        $baseUrl .= '&search=' . urlencode($search);
                    }
                    $startPage = max(1, $currentPage - 2);
                    $endPage = min($totalPage, $currentPage + 2);
                    ?>
                    <div class="pagination">
                        <a href="<?= $baseUrl ?>&page=1">首页</a>
                        <a href="<?= $baseUrl ?>&page=<?= max(1, $currentPage - 1) ?>">‹ 上一页</a>
                        
                        <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                            <?php if ($i === $currentPage): ?>
                                <span class="page-num active"><?= $i ?></span>
                            <?php else: ?>
                                <a href="<?= $baseUrl ?>&page=<?= $i ?>" class="page-num"><?= $i ?></a>
                            <?php endif; ?>
                        <?php endfor; ?>
                        
                        <a href="<?= $baseUrl ?>&page=<?= min($totalPage, $currentPage + 1) ?>">下一页 ›</a>
                        <a href="<?= $baseUrl ?>&page=<?= $totalPage ?>">尾页</a>
                        
                        <span style="color:#888;font-size:12px;margin-left:10px;">共 <?= $totalPage ?> 页</span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script><?php include __DIR__ . '/../js/app.js'; ?></script>

</body>
</html>
