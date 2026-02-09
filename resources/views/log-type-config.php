<!DOCTYPE html>
<html>
<head>
    <title>日志类型管理</title>
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

        /* 头部 + 操作按钮 */
        .cfg-header { display: flex; align-items: center; justify-content: space-between; padding: 0 4px; margin-bottom: 10px; }
        .cfg-header-actions { display: flex; gap: 8px; align-items: center; }
        .cfg-add-btn { padding: 8px 18px; border: none; border-radius: 20px; font-size: 14px; font-weight: 500; cursor: pointer; background: linear-gradient(135deg, #667eea, #764ba2); color: #fff; transition: all .15s; }
        .cfg-add-btn:hover { opacity: .9; }
        .cfg-add-btn:active { transform: scale(.96); opacity: .85; }
        .cfg-io-btn { padding: 8px 16px; border: .5px solid #d1d1d6; border-radius: 20px; font-size: 13px; font-weight: 500; cursor: pointer; background: #fff; color: #667eea; transition: all .15s; }
        .cfg-io-btn:hover { background: #f5f5f7; border-color: #667eea; }
        .cfg-io-btn:active { transform: scale(.96); }

        /* 导入弹窗 */
        .import-zone { border: 2px dashed #d1d1d6; border-radius: 12px; padding: 28px 20px; text-align: center; cursor: pointer; transition: all .2s; position: relative; margin-bottom: 16px; }
        .import-zone:hover, .import-zone.dragover { border-color: #667eea; background: rgba(102,126,234,.04); }
        .import-zone-icon { font-size: 32px; margin-bottom: 8px; opacity: .6; }
        .import-zone-text { font-size: 14px; color: #86868b; }
        .import-zone-text a { color: #667eea; cursor: pointer; text-decoration: underline; }
        .import-zone input[type=file] { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
        .import-textarea { width: 100%; height: 220px; padding: 12px 14px; border: .5px solid #d1d1d6; border-radius: 10px; font-size: 13px; font-family: 'Consolas', 'Monaco', monospace; color: #1d1d1f; background: #fafafa; box-sizing: border-box; resize: vertical; transition: border-color .2s; }
        .import-textarea:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3.5px rgba(102,126,234,.2); background: #fff; }
        .import-textarea::placeholder { color: #c7c7cc; }
        .import-tabs { display: flex; gap: 0; margin-bottom: 16px; border-radius: 10px; overflow: hidden; border: .5px solid #d1d1d6; }
        .import-tab { flex: 1; padding: 10px; text-align: center; font-size: 13px; font-weight: 500; cursor: pointer; background: #f5f5f7; color: #86868b; border: none; transition: all .15s; }
        .import-tab.active { background: #667eea; color: #fff; }
        .import-tab:hover:not(.active) { background: #e8e8ed; }
        .import-preview { margin-top: 12px; padding: 12px 14px; background: #f0f8f0; border-radius: 10px; font-size: 13px; color: #2e7d32; display: none; }
        .import-preview.error { background: #fef0f0; color: #c62828; }
        .import-preview.show { display: block; }

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
    <p>日志类型管理</p>
</div>

<!-- Tab 导航 -->
<div class="nav-tabs">
    <a class="nav-link" href="<?= $_basePath ?>">📄 普通日志</a>
    <?php foreach ($logTypes as $lt): ?>
        <a class="nav-link" href="<?= $_basePath ?>?mode=<?= htmlspecialchars($lt['mode']) ?>"><?= htmlspecialchars(($lt['icon'] ?? '📋') . ' ' . $lt['name']) ?></a>
    <?php endforeach; ?>
    <a class="nav-link active" href="<?= $_basePath ?>/config" style="margin-left: auto;">⚙️ 类型管理</a>
</div>

<div class="cfg-wrap">
    <!-- 区域：已配置类型 -->
    <div class="cfg-header">
        <div class="sec-title">已配置的日志类型</div>
        <div class="cfg-header-actions">
            <button class="cfg-io-btn" onclick="openImportModal()">📥 导入</button>
            <button class="cfg-io-btn" onclick="exportTypes()">📤 导出</button>
            <button class="cfg-add-btn" onclick="openAddModal()">+ 添加类型</button>
        </div>
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
        <div class="help-item"><span class="help-num">1</span><strong>日志通道</strong> — 对应日志 JSON 中 <code>channel</code> 字段值，如 <code>landing_page</code></div>
        <div class="help-item"><span class="help-num">2</span><strong>聚合字段</strong> — <code>context</code> 中用于分组的字段名，如 <code>task_id</code>，同一值的日志会聚合为一条记录</div>
        <div class="help-item"><span class="help-num">3</span><strong>聚合值前缀</strong> — 聚合字段值的固定前缀（如 <code>TASK-</code>），用于预筛选日志行提升扫描性能</div>
        <div class="help-item"><span class="help-num">4</span><strong>阶段定义</strong> — 可选，对应 <code>context.stage</code>，详情页按阶段分组展示执行链路</div>
        <div class="help-item"><span class="help-num">5</span><strong>成功阶段</strong> — 可选，指定哪个 stage 代表任务完成，用于标记聚合项状态为「已完成」</div>
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
                        <label class="fg-label">聚合值前缀</label>
                        <input class="fg-input" type="text" id="formGrepPattern" placeholder="如 TASK-，用于快速过滤日志行">
                        <div class="fg-hint">聚合字段值的固定前缀，如 <code>TASK-</code>，用于预筛选含有该关键字的日志行</div>
                    </div>
                </div>
                <div class="fg">
                    <label class="fg-label">成功阶段标识</label>
                    <input class="fg-input" type="text" id="formSuccessStage" placeholder="可选，用于判断任务是否完成">
                </div>
                <div class="fg">
                    <label class="fg-label">阶段定义</label>
                    <div class="se-wrap">
                        <div class="se-list" id="stageList"></div>
                        <button class="se-add" type="button" onclick="addStageRow()">+ 添加阶段</button>
                    </div>
                </div>
                <!-- 极少使用的字段，默认隐藏 -->
                <button class="adv-toggle" type="button" onclick="toggleMore(this)" style="margin-top: 10px;">
                    <span class="arrow">▶</span> 更多选项
                </button>
                <div class="adv-section" id="moreSection">
                    <div class="fg-row">
                        <div class="fg">
                            <label class="fg-label">文件匹配</label>
                            <input class="fg-input" type="text" id="formFilePattern" placeholder="默认同通道名">
                            <div class="fg-hint">日志文件名包含的关键字，留空自动使用通道名</div>
                        </div>
                        <div class="fg">
                            <label class="fg-label">聚合正则</label>
                            <input class="fg-input" type="text" id="formAggPattern" placeholder="备选方案，通常不需要">
                            <div class="fg-hint">当 context 中无聚合字段时，从日志全文提取</div>
                        </div>
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

<!-- ========== 导入弹窗 ========== -->
<div class="modal-overlay" id="importOverlay">
    <div class="modal" style="width: 580px;">
        <div class="modal-head">
            <span class="modal-title">📥 导入日志类型</span>
            <button class="modal-x" onclick="closeImportModal()">✕</button>
        </div>
        <div class="modal-body">
            <!-- 切换：文件上传 / 粘贴JSON -->
            <div class="import-tabs">
                <button class="import-tab active" onclick="switchImportTab(this, 'paste')">📋 粘贴 JSON</button>
                <button class="import-tab" onclick="switchImportTab(this, 'file')">📁 上传文件</button>
            </div>

            <!-- 粘贴 JSON 区域 -->
            <div id="importPasteZone">
                <div style="display: flex; justify-content: flex-end; margin-bottom: 8px;">
                    <button class="cfg-io-btn" onclick="fillTemplate('simple')" style="font-size: 12px; padding: 5px 12px;">📄 基础模板</button>
                    <button class="cfg-io-btn" onclick="fillTemplate('full')" style="font-size: 12px; padding: 5px 12px; margin-left: 6px;">📑 完整模板</button>
                </div>
                <textarea class="import-textarea" id="importJsonText" placeholder='粘贴 JSON 数组，或点击上方「模板」按钮快速填充示例格式'></textarea>
            </div>

            <!-- 文件上传区域 -->
            <div id="importFileZone" style="display: none;">
                <div class="import-zone" id="dropZone">
                    <input type="file" accept=".json" id="importFile" onchange="handleImportFile(this)">
                    <div class="import-zone-icon">📄</div>
                    <div class="import-zone-text">拖拽 JSON 文件到此处，或 <a>点击选择文件</a></div>
                </div>
            </div>

            <!-- 预览/校验结果 -->
            <div class="import-preview" id="importPreview"></div>

            <!-- 字段说明（可折叠） -->
            <div style="margin-top: 14px;">
                <button class="adv-toggle" type="button" onclick="this.classList.toggle('open'); document.getElementById('fieldRef').classList.toggle('open');">
                    <span class="arrow">▶</span> 字段说明
                </button>
                <div class="adv-section" id="fieldRef">
                    <table style="width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 4px;">
                        <thead>
                            <tr style="background: #f5f5f7; text-align: left;">
                                <th style="padding: 7px 10px; font-weight: 600; color: #1d1d1f; border-bottom: .5px solid #d1d1d6; width: 130px;">字段</th>
                                <th style="padding: 7px 10px; font-weight: 600; color: #1d1d1f; border-bottom: .5px solid #d1d1d6; width: 40px;">必填</th>
                                <th style="padding: 7px 10px; font-weight: 600; color: #1d1d1f; border-bottom: .5px solid #d1d1d6;">说明</th>
                            </tr>
                        </thead>
                        <tbody style="color: #444;">
                            <tr><td style="padding: 6px 10px; border-bottom: .5px solid #eee;"><code style="background:#f0f2f5; padding:1px 5px; border-radius:3px; color:#764ba2;">name</code></td><td style="padding: 6px 10px; border-bottom: .5px solid #eee; color: #FF3B30;">✱</td><td style="padding: 6px 10px; border-bottom: .5px solid #eee;">显示名称，如「落地页任务」</td></tr>
                            <tr><td style="padding: 6px 10px; border-bottom: .5px solid #eee;"><code style="background:#f0f2f5; padding:1px 5px; border-radius:3px; color:#764ba2;">log_channel</code></td><td style="padding: 6px 10px; border-bottom: .5px solid #eee; color: #FF3B30;">✱</td><td style="padding: 6px 10px; border-bottom: .5px solid #eee;">日志 JSON 中 <code style="background:#f0f2f5; padding:1px 4px; border-radius:3px;">channel</code> 字段的值</td></tr>
                            <tr><td style="padding: 6px 10px; border-bottom: .5px solid #eee;"><code style="background:#f0f2f5; padding:1px 5px; border-radius:3px; color:#764ba2;">aggregate_field</code></td><td style="padding: 6px 10px; border-bottom: .5px solid #eee; color: #FF3B30;">✱</td><td style="padding: 6px 10px; border-bottom: .5px solid #eee;">context 中用于分组的字段名，如 <code style="background:#f0f2f5; padding:1px 4px; border-radius:3px;">task_id</code></td></tr>
                            <tr><td style="padding: 6px 10px; border-bottom: .5px solid #eee;"><code style="background:#f0f2f5; padding:1px 5px; border-radius:3px; color:#764ba2;">icon</code></td><td style="padding: 6px 10px; border-bottom: .5px solid #eee; color: #86868b;">—</td><td style="padding: 6px 10px; border-bottom: .5px solid #eee;">Emoji 图标，默认 📋</td></tr>
                            <tr><td style="padding: 6px 10px; border-bottom: .5px solid #eee;"><code style="background:#f0f2f5; padding:1px 5px; border-radius:3px; color:#764ba2;">grep_pattern</code></td><td style="padding: 6px 10px; border-bottom: .5px solid #eee; color: #86868b;">—</td><td style="padding: 6px 10px; border-bottom: .5px solid #eee;">聚合值的固定前缀，如 <code style="background:#f0f2f5; padding:1px 4px; border-radius:3px;">TASK-</code>，用于预筛选日志行提升性能</td></tr>
                            <tr><td style="padding: 6px 10px; border-bottom: .5px solid #eee;"><code style="background:#f0f2f5; padding:1px 5px; border-radius:3px; color:#764ba2;">stages</code></td><td style="padding: 6px 10px; border-bottom: .5px solid #eee; color: #86868b;">—</td><td style="padding: 6px 10px; border-bottom: .5px solid #eee;">阶段定义，对应 <code style="background:#f0f2f5; padding:1px 4px; border-radius:3px;">context.stage</code>，含 step/name/color</td></tr>
                            <tr><td style="padding: 6px 10px;"><code style="background:#f0f2f5; padding:1px 5px; border-radius:3px; color:#764ba2;">success_stage</code></td><td style="padding: 6px 10px; color: #86868b;">—</td><td style="padding: 6px 10px;">成功阶段的 key，用于判断任务状态为「已完成」</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="fg-hint" style="margin-top: 10px;">
                支持导入 JSON 数组（多个类型）或单个 JSON 对象。已存在的类型（相同 ID 或 mode）将被覆盖更新。
            </div>
        </div>
        <div class="modal-foot">
            <button class="m-btn m-btn-cancel" onclick="closeImportModal()">取消</button>
            <button class="m-btn m-btn-save" onclick="submitImport()">确认导入</button>
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

function toggleMore(btn) {
    btn.classList.toggle('open');
    document.getElementById('moreSection').classList.toggle('open');
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
    // 收起高级设置 & 更多选项
    document.querySelector('#modalOverlay .adv-toggle').classList.remove('open');
    document.getElementById('advSection').classList.remove('open');
    var moreBtns = document.querySelectorAll('#advSection .adv-toggle');
    if (moreBtns.length > 0) moreBtns[0].classList.remove('open');
    document.getElementById('moreSection').classList.remove('open');
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
    var hasAdv = data.grep_pattern || data.success_stage || (data.stages && Object.keys(data.stages).length > 0) || data.aggregate_pattern || data.file_pattern;
    var hasMore = data.aggregate_pattern || (data.file_pattern && data.file_pattern !== data.log_channel);
    if (data.stages) {
        for (var k in data.stages) {
            if (data.stages.hasOwnProperty(k)) {
                addStageRow(k, data.stages[k].name || '', data.stages[k].step || 0, data.stages[k].color || '#667eea');
            }
        }
    }
    // 如果有高级字段内容，自动展开
    var advBtn = document.querySelector('#modalOverlay .adv-toggle');
    var advSec = document.getElementById('advSection');
    if (hasAdv) { advBtn.classList.add('open'); advSec.classList.add('open'); }
    else { advBtn.classList.remove('open'); advSec.classList.remove('open'); }
    // 如果有更多选项字段内容，自动展开
    var moreBtns = document.querySelectorAll('#advSection .adv-toggle');
    var moreSec = document.getElementById('moreSection');
    if (moreBtns.length > 0) {
        if (hasMore) { moreBtns[0].classList.add('open'); moreSec.classList.add('open'); }
        else { moreBtns[0].classList.remove('open'); moreSec.classList.remove('open'); }
    }
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

// ==================== 导入/导出 ====================

function openImportModal() {
    document.getElementById('importJsonText').value = '';
    document.getElementById('importFile').value = '';
    var preview = document.getElementById('importPreview');
    preview.className = 'import-preview';
    preview.textContent = '';
    document.getElementById('importOverlay').classList.add('active');
}

function closeImportModal() {
    document.getElementById('importOverlay').classList.remove('active');
}

document.getElementById('importOverlay').addEventListener('click', function(e) {
    if (e.target === this) closeImportModal();
});

function switchImportTab(btn, tab) {
    document.querySelectorAll('.import-tab').forEach(function(t) { t.classList.remove('active'); });
    btn.classList.add('active');
    document.getElementById('importPasteZone').style.display = (tab === 'paste') ? 'block' : 'none';
    document.getElementById('importFileZone').style.display = (tab === 'file') ? 'block' : 'none';
}

function handleImportFile(input) {
    var file = input.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var text = e.target.result;
        document.getElementById('importJsonText').value = text;
        // 切换到粘贴视图展示内容
        document.querySelectorAll('.import-tab').forEach(function(t) { t.classList.remove('active'); });
        document.querySelectorAll('.import-tab')[0].classList.add('active');
        document.getElementById('importPasteZone').style.display = 'block';
        document.getElementById('importFileZone').style.display = 'none';
        validateImportJson(text);
    };
    reader.readAsText(file);
}

function validateImportJson(text) {
    var preview = document.getElementById('importPreview');
    try {
        var data = JSON.parse(text);
        if (!Array.isArray(data)) {
            if (data.name || data.id) {
                data = [data];
            } else {
                preview.className = 'import-preview error show';
                preview.textContent = '❌ 无效格式：需要 JSON 数组或包含 name 字段的对象';
                return null;
            }
        }
        var names = data.map(function(d) { return d.name || '未命名'; });
        preview.className = 'import-preview show';
        preview.innerHTML = '✅ 检测到 <strong>' + data.length + '</strong> 个日志类型：' + names.join('、');
        return data;
    } catch (e) {
        preview.className = 'import-preview error show';
        preview.textContent = '❌ JSON 解析失败：' + e.message;
        return null;
    }
}

// 粘贴区域实时校验
document.getElementById('importJsonText').addEventListener('input', function() {
    var text = this.value.trim();
    if (text) validateImportJson(text);
    else {
        var preview = document.getElementById('importPreview');
        preview.className = 'import-preview';
        preview.textContent = '';
    }
});

// 拖拽支持
var dropZone = document.getElementById('dropZone');
if (dropZone) {
    dropZone.addEventListener('dragover', function(e) { e.preventDefault(); this.classList.add('dragover'); });
    dropZone.addEventListener('dragleave', function() { this.classList.remove('dragover'); });
    dropZone.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        var file = e.dataTransfer.files[0];
        if (file && file.name.endsWith('.json')) {
            document.getElementById('importFile').files = e.dataTransfer.files;
            handleImportFile(document.getElementById('importFile'));
        } else {
            showToast('请拖入 .json 文件', 'error');
        }
    });
}

function submitImport() {
    var text = document.getElementById('importJsonText').value.trim();
    if (!text) {
        showToast('请输入或上传 JSON 数据', 'error');
        return;
    }
    var data = validateImportJson(text);
    if (!data) return;

    fetch(basePath + '/config/import', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: text
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            showToast(res.message || '导入成功');
            closeImportModal();
            setTimeout(function() { location.reload(); }, 800);
        } else {
            showToast(res.message || '导入失败', 'error');
        }
    })
    .catch(function(e) { showToast('请求失败: ' + e.message, 'error'); });
}

function exportTypes() {
    window.open(basePath + '/config/export', '_blank');
}

// ==================== 导入模板 ====================

var IMPORT_TEMPLATES = {
    simple: [
        {
            "name": "示例任务",
            "log_channel": "example_channel",
            "aggregate_field": "task_id"
        }
    ],
    full: [
        {
            "name": "示例任务",
            "icon": "🎨",
            "log_channel": "example_channel",
            "aggregate_field": "task_id",
            "grep_pattern": "TASK-",
            "stages": {
                "task_create": {
                    "step": 1,
                    "name": "任务创建",
                    "color": "#3F51B5"
                },
                "processing": {
                    "step": 2,
                    "name": "处理中",
                    "color": "#FF9800"
                },
                "task_complete": {
                    "step": 3,
                    "name": "任务完成",
                    "color": "#4CAF50"
                }
            },
            "success_stage": "task_complete"
        }
    ]
};

function fillTemplate(type) {
    var textarea = document.getElementById('importJsonText');
    var tpl = IMPORT_TEMPLATES[type] || IMPORT_TEMPLATES.simple;
    textarea.value = JSON.stringify(tpl, null, 4);
    validateImportJson(textarea.value);
}
</script>

</body>
</html>
