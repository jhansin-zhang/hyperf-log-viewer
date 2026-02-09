<!DOCTYPE html>
<html>
<head>
    <title>日志类型配置</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <style>
        <?php include __DIR__ . '/../css/style.css'; ?>

        /* ========== Config Page ========== */

        .cfg-wrap { max-width: 980px; margin: 0 auto; padding: 24px 20px 60px; }

        /* 区域标题 */
        .sec-title { font-size: 13px; font-weight: 600; color: #86868b; text-transform: uppercase; letter-spacing: .5px; padding: 0 4px 10px; }

        /* 卡片组 */
        .cfg-card { background: #fff; border-radius: 14px; box-shadow: 0 0 0 0.5px rgba(0,0,0,.08), 0 2px 8px rgba(0,0,0,.04); margin-bottom: 28px; overflow: hidden; }
        
        /* 列表项 */
        .cfg-item { display: flex; align-items: center; gap: 14px; padding: 14px 20px; transition: background .15s; cursor: default; position: relative; }
        .cfg-item + .cfg-item { border-top: .5px solid rgba(0,0,0,.08); }
        .cfg-item:hover { background: #fafafa; }
        .cfg-item:active { background: #f0f0f2; }
        
        .cfg-icon { width: 42px; height: 42px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; background: linear-gradient(135deg, #764ba2, #e91e63); }

        .cfg-body { flex: 1; min-width: 0; }
        .cfg-name { font-size: 15px; font-weight: 500; color: #1d1d1f; line-height: 1.3; display: flex; align-items: center; gap: 8px; }
        .cfg-desc { font-size: 12px; color: #86868b; margin-top: 2px; display: flex; gap: 12px; flex-wrap: wrap; }
        .cfg-desc code { background: #f0f2f5; padding: 1px 6px; border-radius: 4px; font-family: 'Consolas', 'Monaco', monospace; font-size: 11px; color: #333; }

        .cfg-actions { display: flex; gap: 6px; flex-shrink: 0; }
        .cfg-btn { padding: 6px 14px; border: none; border-radius: 8px; font-size: 13px; font-weight: 500; cursor: pointer; transition: all .15s; background: #f5f5f7; color: #667eea; }
        .cfg-btn:hover { background: #e8e8ed; }
        .cfg-btn:active { transform: scale(.96); }
        .cfg-btn-del { color: #FF3B30; }
        .cfg-btn-del:hover { background: #ffebee; }

        /* 头部 + 添加按钮 */
        .cfg-header { display: flex; align-items: center; justify-content: space-between; padding: 0 4px; margin-bottom: 10px; }
        .cfg-add-btn { padding: 8px 18px; border: none; border-radius: 20px; font-size: 14px; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; transition: all .15s; }
        .cfg-add-btn:hover { opacity: .9; }
        .cfg-add-btn:active { transform: scale(.96); opacity: .85; }

        /* 使用说明 */
        .help-item { padding: 14px 20px; font-size: 13px; color: #1d1d1f; line-height: 1.65; }
        .help-item + .help-item { border-top: .5px solid rgba(0,0,0,.06); }
        .help-item strong { font-weight: 600; color: #1d1d1f; }
        .help-item code { background: #f0f2f5; padding: 2px 7px; border-radius: 5px; font-family: 'Consolas', 'Monaco', monospace; font-size: 12px; color: #764ba2; }
        .help-num { display: inline-flex; align-items: center; justify-content: center; width: 20px; height: 20px; border-radius: 50%; background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; font-size: 11px; font-weight: 700; margin-right: 8px; flex-shrink: 0; }

        /* 空状态 */
        .cfg-empty { text-align: center; padding: 50px 20px; }
        .cfg-empty-icon { font-size: 48px; margin-bottom: 12px; opacity: .5; }
        .cfg-empty h3 { font-size: 17px; font-weight: 600; color: #1d1d1f; margin: 0 0 6px; }
        .cfg-empty p { font-size: 13px; color: #86868b; margin: 0; }

        /* ========== Modal - Apple Sheet Style ========== */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 1000; justify-content: center; align-items: flex-start; padding-top: 50px; }
        .modal-overlay.active { display: flex; }
        
        .modal { background: #fff; border-radius: 14px; width: 520px; max-width: 94vw; max-height: 88vh; overflow-y: auto; box-shadow: 0 24px 80px rgba(0,0,0,.2), 0 0 0 .5px rgba(0,0,0,.1); }
        .modal::-webkit-scrollbar { width: 6px; }
        .modal::-webkit-scrollbar-thumb { background: rgba(0,0,0,.15); border-radius: 3px; }
        
        .modal-head { padding: 20px 24px 16px; display: flex; align-items: center; justify-content: space-between; }
        .modal-title { font-size: 19px; font-weight: 700; color: #1d1d1f; }
        .modal-x { background: #e8e8ed; border: none; width: 28px; height: 28px; border-radius: 50%; font-size: 14px; line-height: 1; color: #86868b; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; }
        .modal-x:hover { background: #d1d1d6; color: #1d1d1f; }
        
        .modal-body { padding: 0 24px 20px; }
        
        /* 表单 - Apple Settings Style */
        .fg { margin-bottom: 20px; }
        .fg-label { font-size: 13px; font-weight: 600; color: #1d1d1f; margin-bottom: 6px; display: block; }
        .fg-label .req { color: #FF3B30; margin-left: 2px; }
        .fg-input { width: 100%; padding: 10px 14px; border: .5px solid #d1d1d6; border-radius: 10px; font-size: 15px; font-family: inherit; color: #1d1d1f; background: #fff; box-sizing: border-box; transition: all .2s; -webkit-appearance: none; }
        .fg-input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3.5px rgba(102,126,234,.2); }
        .fg-input::placeholder { color: #c7c7cc; }
        .fg-hint { font-size: 12px; color: #86868b; margin-top: 5px; line-height: 1.4; }
        .fg-row { display: flex; gap: 14px; }
        .fg-row .fg { flex: 1; }

        /* 阶段编辑器 */
        .se-wrap { border: .5px solid #d1d1d6; border-radius: 10px; overflow: hidden; }
        .se-list { padding: 10px; display: flex; flex-direction: column; gap: 6px; }
        .se-row { display: flex; align-items: center; gap: 6px; padding: 8px 10px; background: #f5f5f7; border-radius: 8px; }
        .se-row input { padding: 7px 10px; border: .5px solid #d1d1d6; border-radius: 7px; font-size: 13px; font-family: inherit; background: #fff; color: #1d1d1f; box-sizing: border-box; }
        .se-row input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,.15); }
        .se-row .sk { flex: 2; }
        .se-row .sn { flex: 2; }
        .se-row .ss { width: 52px; text-align: center; flex: none; }
        .se-row .sc { width: 38px; height: 32px; padding: 2px; border-radius: 6px; cursor: pointer; flex: none; border: .5px solid #d1d1d6; }
        .se-rm { background: none; border: none; color: #FF3B30; font-size: 18px; cursor: pointer; padding: 2px 6px; border-radius: 6px; flex: none; line-height: 1; }
        .se-rm:hover { background: #ffebee; }
        .se-add { display: block; width: 100%; padding: 10px; background: none; border: none; border-top: .5px solid rgba(0,0,0,.06); font-size: 14px; font-weight: 500; color: #667eea; cursor: pointer; text-align: center; transition: background .15s; }
        .se-add:hover { background: #f5f5f7; }

        /* 高级设置折叠 */
        .adv-toggle { display: flex; align-items: center; gap: 6px; padding: 10px 0; margin-bottom: 14px; border: none; background: none; cursor: pointer; font-size: 13px; font-weight: 500; color: #86868b; transition: color .15s; }
        .adv-toggle:hover { color: #667eea; }
        .adv-toggle .arrow { font-size: 10px; transition: transform .2s; }
        .adv-toggle.open .arrow { transform: rotate(90deg); }
        .adv-section { display: none; padding-top: 4px; border-top: .5px solid #e8e8ed; }
        .adv-section.open { display: block; }

        /* Modal 底部 */
        .modal-foot { padding: 14px 24px 20px; display: flex; justify-content: flex-end; gap: 10px; }
        .m-btn { padding: 10px 22px; border: none; border-radius: 10px; font-size: 15px; font-weight: 500; cursor: pointer; transition: all .15s; }
        .m-btn-cancel { background: #f5f5f7; color: #1d1d1f; }
        .m-btn-cancel:hover { background: #e8e8ed; }
        .m-btn-save { background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; }
        .m-btn-save:hover { opacity: .9; }
        .m-btn:active { transform: scale(.97); }

        /* Toast */
        .toast { position: fixed; top: 20px; left: 50%; transform: translateX(-50%) translateY(-120px); padding: 12px 24px; border-radius: 12px; font-size: 14px; font-weight: 500; z-index: 2000; transition: transform .35s cubic-bezier(.4,0,.2,1); pointer-events: none; }
        .toast.show { transform: translateX(-50%) translateY(0); }
        .toast-success { background: rgba(76,175,80,.95); color: #fff; backdrop-filter: blur(10px); box-shadow: 0 4px 20px rgba(76,175,80,.3); }
        .toast-error { background: rgba(229,57,53,.95); color: #fff; backdrop-filter: blur(10px); box-shadow: 0 4px 20px rgba(229,57,53,.3); }
    </style>
</head>
<body>

<!-- 页面头部 -->
<div class="page-header">
    <h1>📋 日志查看器</h1>
    <p>配置管理</p>
</div>

<!-- Tab 导航 -->
<div class="nav-tabs">
    <a class="nav-link" href="<?= $_basePath ?>">📄 普通日志</a>
    <?php foreach ($logTypes as $lt): ?>
        <a class="nav-link" href="<?= $_basePath ?>?mode=<?= htmlspecialchars($lt['mode']) ?>"><?= htmlspecialchars(($lt['icon'] ?? '📋') . ' ' . $lt['name']) ?></a>
    <?php endforeach; ?>
    <a class="nav-link active" href="<?= $_basePath ?>/config" style="margin-left: auto;">⚙️ 配置</a>
</div>

<div class="cfg-wrap">
    <!-- 区域：已配置类型 -->
    <div class="cfg-header">
        <div class="sec-title">已配置的日志类型</div>
        <button class="cfg-add-btn" onclick="openAddModal()">+ 添加类型</button>
    </div>
    
    <div class="cfg-card">
        <?php if (empty($logTypes)): ?>
            <div class="cfg-empty">
                <div class="cfg-empty-icon">📭</div>
                <h3>暂无配置</h3>
                <p>点击右上方「添加类型」开始配置</p>
            </div>
        <?php else: ?>
            <?php foreach ($logTypes as $lt): ?>
                <div class="cfg-item">
                    <div class="cfg-icon">
                        <?= htmlspecialchars($lt['icon'] ?? '📋') ?>
                    </div>
                    <div class="cfg-body">
                        <div class="cfg-name">
                            <?= htmlspecialchars($lt['name']) ?>
                        </div>
                        <div class="cfg-desc">
                            <span>模式 <code><?= htmlspecialchars($lt['mode']) ?></code></span>
                            <span>通道 <code><?= htmlspecialchars($lt['log_channel']) ?></code></span>
                            <span>聚合 <code><?= htmlspecialchars($lt['aggregate_field']) ?></code></span>
                            <?php if (!empty($lt['stages'])): ?>
                                <span><?= count($lt['stages']) ?> 个阶段</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="cfg-actions">
                        <button class="cfg-btn" onclick='editType(<?= json_encode($lt, JSON_UNESCAPED_UNICODE) ?>)'>编辑</button>
                        <button class="cfg-btn cfg-btn-del" onclick="deleteType('<?= htmlspecialchars($lt['id']) ?>', '<?= htmlspecialchars($lt['name']) ?>')">删除</button>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- 区域：使用说明 -->
    <div class="sec-title">使用说明</div>
    <div class="cfg-card">
        <div class="help-item"><span class="help-num">1</span><strong>日志通道</strong> — 对应日志 JSON 中 <code>channel</code> 字段，如 <code>landing_page</code></div>
        <div class="help-item"><span class="help-num">2</span><strong>文件匹配</strong> — 日志文件名包含的关键字，用于快速筛选扫描范围</div>
        <div class="help-item"><span class="help-num">3</span><strong>聚合字段</strong> — <code>context</code> 中用于分组的字段名，如 <code>task_id</code></div>
        <div class="help-item"><span class="help-num">4</span><strong>聚合模式</strong> — 正则表达式，从日志内容提取聚合值（备选方案）</div>
        <div class="help-item"><span class="help-num">5</span><strong>Grep 关键字</strong> — Shell grep 快速过滤的关键字，提升性能</div>
        <div class="help-item"><span class="help-num">6</span><strong>阶段定义</strong> — 可选，对应 <code>context.stage</code>，详情页按阶段分组展示</div>
    </div>
</div>

<!-- ========== 添加/编辑弹窗 ========== -->
<div class="modal-overlay" id="modalOverlay">
    <div class="modal">
        <div class="modal-head">
            <span class="modal-title" id="modalTitle">添加日志类型</span>
            <button class="modal-x" onclick="closeModal()">✕</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="formId" value="">
            <input type="hidden" id="formCreatedAt" value="">
            <input type="hidden" id="formMode" value="">
            
            <!-- 核心：只需填 3 个字段 -->
            <div class="fg">
                <label class="fg-label">名称<span class="req">*</span></label>
                <input class="fg-input" type="text" id="formName" placeholder="例如：落地页任务">
            </div>
            
            <div class="fg">
                <label class="fg-label">日志通道<span class="req">*</span></label>
                <input class="fg-input" type="text" id="formLogChannel" placeholder="例如：landing_page" list="channelList">
                <datalist id="channelList">
                    <?php foreach ($availableChannels as $ch): ?>
                        <option value="<?= htmlspecialchars($ch) ?>">
                    <?php endforeach; ?>
                </datalist>
                <div class="fg-hint">日志 JSON 中的 channel 字段</div>
            </div>
            
            <div class="fg">
                <label class="fg-label">聚合字段<span class="req">*</span></label>
                <input class="fg-input" type="text" id="formAggField" placeholder="例如：task_id" value="task_id">
                <div class="fg-hint">按此字段对日志分组展示</div>
            </div>

            <!-- 高级设置（折叠） -->
            <button class="adv-toggle" type="button" onclick="toggleAdvanced(this)">
                <span class="arrow">▶</span> 高级设置
            </button>
            <div class="adv-section" id="advSection">
                <div class="fg-row">
                    <div class="fg">
                        <label class="fg-label">图标</label>
                        <input class="fg-input" type="text" id="formIcon" placeholder="📋" style="text-align: center; font-size: 20px; padding: 7px;">
                    </div>
                    <div class="fg">
                        <label class="fg-label">文件匹配</label>
                        <input class="fg-input" type="text" id="formFilePattern" placeholder="自动使用通道名">
                    </div>
                </div>
                <div class="fg-row">
                    <div class="fg">
                        <label class="fg-label">聚合正则</label>
                        <input class="fg-input" type="text" id="formAggPattern" placeholder="可选">
                    </div>
                    <div class="fg">
                        <label class="fg-label">Grep 关键字</label>
                        <input class="fg-input" type="text" id="formGrepPattern" placeholder="可选">
                    </div>
                </div>
                <div class="fg">
                    <label class="fg-label">成功阶段标识</label>
                    <input class="fg-input" type="text" id="formSuccessStage" placeholder="可选">
                </div>
                <div class="fg">
                    <label class="fg-label">阶段定义</label>
                    <div class="se-wrap">
                        <div class="se-list" id="stageList"></div>
                        <button class="se-add" type="button" onclick="addStageRow()">+ 添加阶段</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="m-btn m-btn-cancel" onclick="closeModal()">取消</button>
            <button class="m-btn m-btn-save" onclick="saveType()">保存</button>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="toast" id="toast"></div>

<script>
var basePath = '<?= $_basePath ?>';

function showToast(msg, type) {
    var t = document.getElementById('toast');
    t.textContent = msg;
    t.className = 'toast toast-' + (type || 'success') + ' show';
    setTimeout(function() { t.className = 'toast'; }, 2500);
}

function toggleAdvanced(btn) {
    btn.classList.toggle('open');
    document.getElementById('advSection').classList.toggle('open');
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = '添加日志类型';
    document.getElementById('formId').value = '';
    document.getElementById('formCreatedAt').value = '';
    document.getElementById('formName').value = '';
    document.getElementById('formIcon').value = '📋';
    document.getElementById('formMode').value = '';
    document.getElementById('formLogChannel').value = '';
    document.getElementById('formFilePattern').value = '';
    document.getElementById('formAggField').value = 'task_id';
    document.getElementById('formAggPattern').value = '';
    document.getElementById('formGrepPattern').value = '';
    document.getElementById('formSuccessStage').value = '';
    document.getElementById('stageList').innerHTML = '';
    // 收起高级设置
    document.querySelector('.adv-toggle').classList.remove('open');
    document.getElementById('advSection').classList.remove('open');
    document.getElementById('modalOverlay').classList.add('active');
}

function editType(data) {
    document.getElementById('modalTitle').textContent = '编辑日志类型';
    document.getElementById('formId').value = data.id || '';
    document.getElementById('formCreatedAt').value = data.created_at || '';
    document.getElementById('formName').value = data.name || '';
    document.getElementById('formIcon').value = data.icon || '📋';
    document.getElementById('formMode').value = data.mode || '';
    document.getElementById('formLogChannel').value = data.log_channel || '';
    document.getElementById('formFilePattern').value = data.file_pattern || '';
    document.getElementById('formAggField').value = data.aggregate_field || '';
    document.getElementById('formAggPattern').value = data.aggregate_pattern || '';
    document.getElementById('formGrepPattern').value = data.grep_pattern || '';
    document.getElementById('formSuccessStage').value = data.success_stage || '';
    var stageList = document.getElementById('stageList');
    stageList.innerHTML = '';
    var hasAdv = data.aggregate_pattern || data.grep_pattern || data.success_stage || (data.stages && Object.keys(data.stages).length > 0);
    if (data.stages) {
        for (var k in data.stages) {
            if (data.stages.hasOwnProperty(k)) {
                addStageRow(k, data.stages[k].name || '', data.stages[k].step || 0, data.stages[k].color || '#667eea');
            }
        }
    }
    // 如果有高级字段内容，自动展开
    var advBtn = document.querySelector('.adv-toggle');
    var advSec = document.getElementById('advSection');
    if (hasAdv) { advBtn.classList.add('open'); advSec.classList.add('open'); }
    else { advBtn.classList.remove('open'); advSec.classList.remove('open'); }
    document.getElementById('modalOverlay').classList.add('active');
}

function closeModal() {
    document.getElementById('modalOverlay').classList.remove('active');
}

function addStageRow(key, name, step, color) {
    var list = document.getElementById('stageList');
    var row = document.createElement('div');
    row.className = 'se-row';
    row.innerHTML = '<input type="text" class="sk" placeholder="stage key" value="' + (key || '') + '">' +
        '<input type="text" class="sn" placeholder="显示名称" value="' + (name || '') + '">' +
        '<input type="number" class="ss" placeholder="#" value="' + (step || (list.children.length + 1)) + '">' +
        '<input type="color" class="sc" value="' + (color || '#667eea') + '">' +
        '<button class="se-rm" onclick="this.parentElement.remove()">✕</button>';
    list.appendChild(row);
}

function collectStages() {
    var stages = {};
    document.querySelectorAll('#stageList .se-row').forEach(function(row) {
        var key = row.querySelector('.sk').value.trim();
        var name = row.querySelector('.sn').value.trim();
        var step = parseInt(row.querySelector('.ss').value) || 0;
        var color = row.querySelector('.sc').value;
        if (key) stages[key] = { step: step, name: name, color: color };
    });
    return stages;
}

function saveType() {
    var channel = document.getElementById('formLogChannel').value.trim();
    var mode = document.getElementById('formMode').value.trim();
    var filePat = document.getElementById('formFilePattern').value.trim();
    // 自动生成：mode 从通道名派生（下划线转短横线）
    if (!mode && channel) mode = channel.replace(/_/g, '-');
    // 自动生成：file_pattern 默认等于通道名
    if (!filePat && channel) filePat = channel;
    var data = {
        id: document.getElementById('formId').value,
        name: document.getElementById('formName').value,
        icon: document.getElementById('formIcon').value || '📋',
        mode: mode,
        log_channel: channel,
        file_pattern: filePat,
        aggregate_field: document.getElementById('formAggField').value,
        aggregate_pattern: document.getElementById('formAggPattern').value,
        grep_pattern: document.getElementById('formGrepPattern').value,
        success_stage: document.getElementById('formSuccessStage').value,
        stages: collectStages(),
        created_at: document.getElementById('formCreatedAt').value
    };
    fetch(basePath + '/config/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            showToast('保存成功');
            setTimeout(function() { location.reload(); }, 800);
        } else {
            showToast(res.message || '保存失败', 'error');
        }
    })
    .catch(function(e) { showToast('请求失败: ' + e.message, 'error'); });
}

function deleteType(id, name) {
    if (!confirm('确定删除「' + name + '」？此操作不可撤销。')) return;
    fetch(basePath + '/config/delete', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: id })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            showToast('已删除');
            setTimeout(function() { location.reload(); }, 600);
        } else {
            showToast(res.message || '删除失败', 'error');
        }
    })
    .catch(function(e) { showToast('请求失败: ' + e.message, 'error'); });
}

document.getElementById('modalOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>

</body>
</html>
