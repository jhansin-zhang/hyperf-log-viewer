<!DOCTYPE html>
<html>
<head>
    <title><?= htmlspecialchars($logType['name']) ?> - 日志查看器</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <style>
        <?php include __DIR__ . '/../css/style.css'; ?>
        <?php include __DIR__ . '/../css/aggregate-log.css'; ?>
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body style="background: #f0f2f5; margin: 0; padding: 0;">

<!-- 头部 -->
<div class="lp-header">
    <h1><?= htmlspecialchars(($logType['icon'] ?? '📋') . ' ' . $logType['name']) ?></h1>
    <p>按 <?= htmlspecialchars($logType['aggregate_field']) ?> 聚合查看完整执行链路</p>
</div>

<!-- Tab 切换 -->
<div class="nav-tabs">
    <a href="?">📄 普通日志</a>
    <?php 
    foreach ($logTypes as $__lt): 
        $__isActive = ($__lt['mode'] === $logType['mode']);
    ?>
        <a href="?mode=<?= htmlspecialchars($__lt['mode']) ?>" class="<?= $__isActive ? 'active' : '' ?>"><?= htmlspecialchars(($__lt['icon'] ?? '📋') . ' ' . $__lt['name']) ?></a>
    <?php endforeach; ?>
    <a href="<?= $_basePath ?>/config" style="margin-left: auto; background: #f0f0f0; color: #666; font-size: 13px;">⚙️ 类型管理</a>
</div>

<div style="padding: 0 20px 20px;">
<?php 
// 加载 Stage 定义
$STAGE_ORDER = $logType['stages'] ?? [];

// 辅助函数
if (!function_exists('getStageColor')) {
    function getStageColor(string $stage, array $stageOrder): string {
        return $stageOrder[$stage]['color'] ?? '#666';
    }
}
if (!function_exists('getStageName')) {
    function getStageName(string $stage, array $stageOrder): string {
        return $stageOrder[$stage]['name'] ?? $stage;
    }
}
if (!function_exists('getStageStep')) {
    function getStageStep(string $stage, array $stageOrder): int {
        return $stageOrder[$stage]['step'] ?? 0;
    }
}
?>

<?php if (!empty($aggregateId)): ?>
    <!-- 聚合项详情视图 -->
    <a href="?mode=<?= htmlspecialchars($logType['mode']) ?>&date=<?= $date ?>" class="back-link">← 返回列表</a>
    
    <?php
    $error_count = 0;
    foreach ($itemLogs as $log) {
        if ($log['level'] === 'error') $error_count++;
    }
    
    // 按 stage 分组日志
    $grouped_logs = [];
    $other_logs = [];
    foreach ($itemLogs as $log) {
        if (($log['level'] ?? '') === 'debug') continue;
        
        $context = $log['context'] ?? [];
        $stage = is_array($context) ? ($context['stage'] ?? null) : null;
        
        if ($stage && isset($STAGE_ORDER[$stage])) {
            if (!isset($grouped_logs[$stage])) {
                $grouped_logs[$stage] = [];
            }
            $grouped_logs[$stage][] = $log;
        } else {
            $other_logs[] = $log;
        }
    }
    
    // 按 step 排序
    uksort($grouped_logs, function($a, $b) use ($STAGE_ORDER) {
        $stepA = $STAGE_ORDER[$a]['step'] ?? 99;
        $stepB = $STAGE_ORDER[$b]['step'] ?? 99;
        return $stepA - $stepB;
    });
    
    if (!empty($other_logs)) {
        $grouped_logs['other'] = $other_logs;
    }
    
    // 如果没有定义 stages，所有日志放在一个组
    if (empty($STAGE_ORDER) && empty($grouped_logs)) {
        $grouped_logs['all'] = $itemLogs;
    }
    ?>
    
    <div class="card">
        <div class="card-header">
            <strong><?= htmlspecialchars($logType['aggregate_field']) ?>:</strong> <code><?= htmlspecialchars($aggregateId) ?></code>
            <span style="margin-left: 20px;"><strong>日志数:</strong> <span id="logCount"><?= count($itemLogs) ?></span></span>
            <?php if ($currentItem): ?>
                <span style="margin-left: 20px;"><strong>时间:</strong> <?= $currentItem['start_time'] ?> ~ <?= $currentItem['end_time'] ?></span>
            <?php endif; ?>
            <button onclick="refreshDetail()" class="refresh-btn" title="刷新日志" style="margin-left: 10px; vertical-align: middle;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 2v6h-6M3 22v-6h6M21 13a9 9 0 01-15.36 6.36M3 11a9 9 0 0115.36-6.36"/>
                </svg>
            </button>
        </div>
        <div class="card-body">
            <?php if (empty($itemLogs)): ?>
                <div class="lp-empty-state">
                    <h3>📭 未找到相关日志</h3>
                    <p>请检查 <?= htmlspecialchars($logType['aggregate_field']) ?> 是否正确</p>
                </div>
            <?php else: ?>
                <!-- Stage 导航 -->
                <?php if (!empty($STAGE_ORDER) || count($grouped_logs) > 1): ?>
                <div style="background: #f8f9fa; padding: 10px 20px; border-bottom: 1px solid #e0e0e0; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
                    <?php foreach ($grouped_logs as $stage => $logs): ?>
                        <?php 
                        $stageName = ($stage === 'other' || $stage === 'all') ? ($stage === 'all' ? '全部日志' : '其他') : getStageName($stage, $STAGE_ORDER);
                        $stageColor = ($stage === 'other' || $stage === 'all') ? '#666' : getStageColor($stage, $STAGE_ORDER);
                        $stageStep = ($stage === 'other' || $stage === 'all') ? '' : getStageStep($stage, $STAGE_ORDER) . '. ';
                        ?>
                        <a href="#stage-<?=$stage?>" class="channel-nav-btn" style="background: <?=$stageColor?>;">
                            <?=$stageStep?><?=$stageName?>
                            <span style="background: rgba(255,255,255,0.3); padding: 2px 8px; border-radius: 10px; font-size: 12px;"><?=count($logs)?></span>
                        </a>
                        <?php 
                        $stageKeys = array_keys($grouped_logs);
                        if ($stage !== end($stageKeys)): ?>
                            <span style="color: #999; font-size: 16px;">→</span>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    
                    <?php if ($error_count > 0): ?>
                        <div style="display: flex; align-items: center; gap: 8px; margin-left: 10px; padding-left: 10px; border-left: 1px solid #ddd;">
                            <span style="color: #c62828; font-size: 13px; font-weight: bold;">❌ <?=$error_count?> 个错误</span>
                            <button onclick="goToNextError()" style="padding: 6px 12px; border-radius: 20px; background: #f44336; color: white; border: none; font-size: 13px; cursor: pointer;">下一个 ▼</button>
                            <span id="errorIndex" style="color: #666; font-size: 12px;">0/<?=$error_count?></span>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                
                <!-- 日志内容 -->
                <div class="log-timeline" id="logTimeline">
                    <?php foreach ($grouped_logs as $stage => $logs): 
                        $stageName = ($stage === 'other' || $stage === 'all') ? ($stage === 'all' ? '全部日志' : '其他') : getStageName($stage, $STAGE_ORDER);
                        $stageColor = ($stage === 'other' || $stage === 'all') ? '#666' : getStageColor($stage, $STAGE_ORDER);
                        $stageStep = ($stage === 'other' || $stage === 'all') ? '' : getStageStep($stage, $STAGE_ORDER) . '. ';
                    ?>
                        <div id="stage-<?=$stage?>" class="channel-section" style="border-bottom: 2px solid <?=$stageColor?>;">
                            <div class="channel-header" style="background: <?=$stageColor?>15; color: <?=$stageColor?>;">
                                <span class="channel-dot" style="background: <?=$stageColor?>;"></span>
                                <?=$stageStep?><?=$stageName?>
                                <span style="font-weight: normal; color: #666; font-size: 13px;">(<?=count($logs)?> 条)</span>
                            </div>
                            <div style="padding: 15px 20px;">
                                <?php foreach ($logs as $log): 
                                    $isError = ($log['level'] ?? '') === 'error';
                                ?>
                                    <div class="log-item <?=$isError?'error-log':''?>">
                                        <div class="log-time">
                                            <span><?= htmlspecialchars($log['time']) ?></span>
                                            <span class="log-stage-badge" style="background: <?=$isError?'#ffebee':$stageColor.'20'?>; color: <?=$isError?'#c62828':$stageColor?>;">
                                                <?=$isError?'错误':htmlspecialchars($log['message'])?>
                                            </span>
                                        </div>
                                        <div class="log-content <?=$log['level']?>" style="border-left: 3px solid <?=$stageColor?>;">
                                            <?= htmlspecialchars($log['message']) ?>
                                            <?php if (!empty($log['context'])): 
                                                $displayContext = $log['context'];
                                                unset($displayContext['step'], $displayContext['stage']);
                                            ?>
                                                <?php if (!empty($displayContext)): ?>
                                                    <div class="log-context"><?= htmlspecialchars(json_encode($displayContext, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></div>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

<?php else: ?>
    <!-- 聚合项列表视图 -->
    <?php
    $todayDate = date('Y-m-d');
    $yesterdayDate = date('Y-m-d', strtotime('-1 day'));
    $dayBeforeDate = date('Y-m-d', strtotime('-2 days'));
    $activeIndex = 0;
    if ($date === $todayDate) $activeIndex = 0;
    elseif ($date === $yesterdayDate) $activeIndex = 1;
    elseif ($date === $dayBeforeDate) $activeIndex = 2;
    else $activeIndex = -1;
    $currentMode = htmlspecialchars($logType['mode']);
    ?>
    
    <div class="filter-bar">
        <form method="get" style="display: flex; align-items: center; flex-wrap: wrap; gap: 10px;">
            <input type="hidden" name="mode" value="<?= $currentMode ?>">
            
            <div class="date-switcher">
                <div class="date-slider" style="<?php if ($activeIndex === -1): ?>display: none;<?php endif; ?> transform: translateX(<?=$activeIndex * 100?>%);"></div>
                <a href="?mode=<?=$currentMode?>&date=<?=$todayDate?>" class="date-btn <?=$activeIndex === 0 ? 'active' : ''?>" data-index="0">今天</a>
                <a href="?mode=<?=$currentMode?>&date=<?=$yesterdayDate?>" class="date-btn <?=$activeIndex === 1 ? 'active' : ''?>" data-index="1">昨天</a>
                <a href="?mode=<?=$currentMode?>&date=<?=$dayBeforeDate?>" class="date-btn <?=$activeIndex === 2 ? 'active' : ''?>" data-index="2">前天</a>
            </div>
            
            <input type="date" name="date" value="<?= $date ?>" onchange="this.form.submit()" style="width: 150px;">
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="搜索 <?= htmlspecialchars($logType['aggregate_field']) ?>..." style="width: 250px;">
            <button type="submit">搜索</button>
            <span style="margin-left: auto; color: #666;">共 <span class="total-count"><?= $total ?></span> 个项目</span>
        </form>
    </div>
    
    <div class="task-list-container">
    <?php if (empty($items)): ?>
        <div class="lp-empty-state">
            <h3>📭 暂无日志记录</h3>
            <p>请选择其他日期或确认日志中包含 <?= htmlspecialchars($logType['aggregate_field']) ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($items as $item): 
            $bg_color = 'linear-gradient(135deg, #ff9800 0%, #f57c00 100%)';
            $shadow_color = 'rgba(255, 152, 0, 0.3)';
            if ($item['status'] === 'success') {
                $bg_color = 'linear-gradient(135deg, #4CAF50 0%, #45a049 100%)';
                $shadow_color = 'rgba(76, 175, 80, 0.3)';
            } elseif ($item['status'] === 'failed') {
                $bg_color = 'linear-gradient(135deg, #f44336 0%, #d32f2f 100%)';
                $shadow_color = 'rgba(244, 67, 54, 0.3)';
            }
            $statusText = match ($item['status']) {
                'success' => '✅ 已完成',
                'failed' => '❌ 已失败',
                default => '⏳ 进行中',
            };
            $statusColor = match ($item['status']) {
                'success' => '#4CAF50',
                'failed' => '#f44336',
                default => '#ff9800',
            };
            
            $shortId = $item['agg_id'];
            if (strlen($shortId) > 30) {
                $shortId = '...' . substr($shortId, -20);
            }
        ?>
            <div class="task-card" onclick="window.location.href='?mode=<?= $currentMode ?>&date=<?= $date ?>&agg_id=<?= urlencode($item['agg_id']) ?>'">
                <div class="task-header">
                    <div class="task-avatar" style="background: <?=$bg_color?>; box-shadow: 0 2px 8px <?=$shadow_color?>; font-size: 18px;">
                        <?= htmlspecialchars($logType['icon'] ?? '📋') ?>
                    </div>
                    <div class="task-info">
                        <div class="task-id">
                            <?= htmlspecialchars($item['agg_id']) ?>
                            <span style="font-size: 12px; margin-left: 10px; font-weight: bold; color: <?=$statusColor?>;">
                                <?=$statusText?>
                            </span>
                        </div>
                        <div class="task-meta">
                            <span>🕐 <?= $item['start_time'] ?> ~ <?= $item['end_time'] ?></span>
                            <span>📝 <?= $item['log_count'] ?> 条</span>
                            <?php if ($item['error_count'] > 0): ?>
                                <span style="color: #f44336;">❌ <?= $item['error_count'] ?> 错误</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div style="color: #c7c7cc; font-size: 20px; padding-left: 10px;">›</div>
                </div>
            </div>
        <?php endforeach; ?>
        
        <!-- 分页 -->
        <?php if ($totalPage > 1): ?>
            <div class="lp-pagination">
                <a href="?mode=<?=$currentMode?>&date=<?= $date ?>&search=<?= urlencode($search) ?>&page=1" class="<?=$page <= 1 ? 'disabled' : ''?>">首页</a>
                <a href="?mode=<?=$currentMode?>&date=<?= $date ?>&search=<?= urlencode($search) ?>&page=<?= $page - 1 ?>" class="<?=$page <= 1 ? 'disabled' : ''?>">上一页</a>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPage, $page + 2); $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?mode=<?=$currentMode?>&date=<?= $date ?>&search=<?= urlencode($search) ?>&page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <a href="?mode=<?=$currentMode?>&date=<?= $date ?>&search=<?= urlencode($search) ?>&page=<?= $page + 1 ?>" class="<?=$page >= $totalPage ? 'disabled' : ''?>">下一页</a>
                <a href="?mode=<?=$currentMode?>&date=<?= $date ?>&search=<?= urlencode($search) ?>&page=<?= $totalPage ?>" class="<?=$page >= $totalPage ? 'disabled' : ''?>">尾页</a>
            </div>
        <?php endif; ?>
    <?php endif; ?>
    </div>
<?php endif; ?>
</div>

<script>
var currentErrorIndex = -1;
var errorElements = [];

$(document).ready(function() {
    errorElements = $('.error-log').toArray();
    
    // 日期快捷切换
    $('.date-btn').on('click', function(e) {
        e.preventDefault();
        var index = $(this).data('index');
        var href = $(this).attr('href');
        var slider = $('.date-slider');
        slider.css('display', 'block');
        slider.css('transform', 'translateX(' + (index * 100) + '%)');
        $('.date-btn').removeClass('active');
        $(this).addClass('active');
        window.location.href = href;
    });
});

function goToNextError() {
    if (errorElements.length === 0) return;
    currentErrorIndex = (currentErrorIndex + 1) % errorElements.length;
    var el = $(errorElements[currentErrorIndex]);
    $('.error-log').css('outline', 'none');
    var container = $('#logTimeline');
    var containerTop = container.offset().top;
    var elTop = el.offset().top;
    var currentScroll = container.scrollTop();
    var targetScroll = currentScroll + (elTop - containerTop) - 50;
    container.animate({ scrollTop: targetScroll }, 300);
    el.css('outline', '3px solid #f44336');
    $('#errorIndex').text((currentErrorIndex + 1) + '/' + errorElements.length);
}

function refreshDetail() {
    var $btn = $('.refresh-btn');
    $btn.addClass('loading');
    $('.card-body').css('opacity', '0.5');
    $.get(window.location.href, function(html) {
        var $newBody = $(html).find('.card-body');
        var newLogCount = $(html).find('#logCount').text();
        $('.card-body').replaceWith($newBody);
        $('#logCount').text(newLogCount);
        $btn.removeClass('loading');
        errorElements = $('.error-log').toArray();
        currentErrorIndex = -1;
    }).fail(function() {
        $btn.removeClass('loading');
        $('.card-body').css('opacity', '1');
    });
}

// 通道导航 - 平滑滚动
$(document).on('click', '.channel-nav-btn', function(e) {
    e.preventDefault();
    var target = $(this).attr('href');
    var $target = $(target);
    if ($target.length) {
        var container = $('#logTimeline');
        var containerTop = container.offset().top;
        var targetTop = $target.offset().top;
        var currentScroll = container.scrollTop();
        var scrollTo = currentScroll + (targetTop - containerTop);
        container.animate({ scrollTop: scrollTo }, 300);
    }
});
</script>

</body>
</html>
