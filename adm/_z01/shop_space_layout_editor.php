<?php
$sub_menu = "930600";
include_once('./_common.php');

@auth_check($auth[$sub_menu],'w');

// 가맹점 접근 권한 체크
$result = check_shop_access();
$shop_id = $result['shop_id'];

$group_id = isset($_GET['group_id']) ? (int)$_GET['group_id'] : 0;

// group_id 검증
if ($group_id <= 0) {
    alert('공간 그룹을 선택해 주세요.');
}

// 공간 그룹 정보
$group_sql = " SELECT * FROM {$g5['shop_space_group_table']} 
               WHERE group_id = {$group_id} AND shop_id = {$shop_id} ";
$group = sql_fetch_pg($group_sql);

if (!$group || !isset($group['group_id'])) {
    alert('존재하지 않는 공간 그룹입니다.');
}

// 도면 이미지
$img_sql = " SELECT * FROM {$g5['dain_file_table']} 
            WHERE fle_db_tbl = 'shop_space_group' 
            AND fle_db_idx = '{$group_id}' 
            AND fle_type = 'ssg' 
            AND fle_dir = 'shop/shop_img' 
            ORDER BY fle_reg_dt DESC 
            LIMIT 1 ";
$img_row = sql_fetch_pg($img_sql);

$background_img_url = '';
if ($img_row && isset($img_row['fle_path'])) {
    $is_s3file_yn = is_s3file($img_row['fle_path']);
    if ($is_s3file_yn) {
        $background_img_url = $set_conf['set_s3_basicurl'].'/'.$img_row['fle_path'];
    }
}

// 공간 유닛 목록 (활성화된 유닛만)
$units_sql = " SELECT * FROM {$g5['shop_space_unit_table']}
               WHERE group_id = {$group_id} AND shop_id = {$shop_id}
               AND is_active = true
               ORDER BY sort_order, unit_id ";
$units_result = sql_query_pg($units_sql);
$units = array();
if ($units_result && is_object($units_result) && isset($units_result->result)) {
    while ($u_row = sql_fetch_array_pg($units_result->result)) {
        $units[] = $u_row;
    }
}

// 캔버스 크기 검증 및 기본값 설정
$canvas_width = isset($group['canvas_width']) ? (int)$group['canvas_width'] : 1000;
$canvas_height = isset($group['canvas_height']) ? (int)$group['canvas_height'] : 800;

// 캔버스 크기 범위 검증
if ($canvas_width < 100 || $canvas_width > 10000) {
    $canvas_width = 1000;
}
if ($canvas_height < 100 || $canvas_height > 10000) {
    $canvas_height = 800;
}

$g5['title'] = '도면 편집 - '.htmlspecialchars($group['name']);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $g5['title'] ?></title>
<style>
* { margin: 0; padding: 0; box-sizing: border-box; }
body {
    font-family: 'Malgun Gothic', '맑은 고딕', sans-serif;
    background: #f5f5f5;
    padding: 20px;
}
.header {
    background: white;
    padding: 15px 20px;
    margin-bottom: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.header h1 {
    font-size: 20px;
    margin-bottom: 10px;
}
.header .info {
    font-size: 14px;
    color: #666;
}
.container {
    display: flex;
    gap: 20px;
}
.canvas-area {
    flex: 1;
    background: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.sidebar {
    width: 300px;
    background: white;
    padding: 20px;
    border-radius: 5px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    max-height: calc(100vh - 180px);
    overflow-y: auto;
}
.unit-list {
    list-style: none;
}
.unit-item {
    padding: 10px;
    margin-bottom: 8px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 3px;
    cursor: pointer;
    transition: background 0.2s;
}
.unit-item:hover {
    background: #e9e9e9;
}
.unit-item.selected {
    background: #d4edff;
    border-color: #0066cc;
}
.unit-name {
    font-weight: bold;
    margin-bottom: 5px;
}
.unit-info {
    font-size: 12px;
    color: #666;
}
.buttons {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #ddd;
}
.btn {
    display: inline-block;
    padding: 10px 20px;
    background: #0066cc;
    color: white;
    text-decoration: none;
    border-radius: 3px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    margin-right: 5px;
}
.btn:hover {
    background: #0052a3;
}
.btn-secondary {
    background: #666;
}
.btn-secondary:hover {
    background: #555;
}
#container {
    border: 1px solid #ddd;
    background: #fff;
}
.info-panel {
    margin-top: 20px;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 3px;
}
.info-panel h3 {
    font-size: 14px;
    margin-bottom: 10px;
}
.info-panel .field {
    margin-bottom: 8px;
    font-size: 13px;
}
.info-panel .field label {
    display: inline-block;
    width: 80px;
    color: #666;
}
</style>
</head>
<body>

<div class="header">
    <h1><?php echo $g5['title'] ?></h1>
    <div class="info">
        캔버스 크기: <?php echo $canvas_width ?> × <?php echo $canvas_height ?> px
        | 공간 유닛: <?php echo count($units) ?>개
    </div>
</div>

<div class="container">
    <div class="canvas-area">
        <div id="container"></div>
    </div>
    
    <div class="sidebar">
        <h3>공간 유닛 목록</h3>
        <ul class="unit-list" id="unit-list">
            <?php foreach ($units as $unit): ?>
            <li class="unit-item" data-unit-id="<?php echo $unit['unit_id'] ?>">
                <div class="unit-name"><?php echo htmlspecialchars($unit['name']) ?></div>
                <div class="unit-info">
                    타입: <?php 
                    $type_map = ['ROOM'=>'룸', 'TABLE'=>'테이블', 'SEAT'=>'좌석', 'VIRTUAL'=>'가상'];
                    echo $type_map[$unit['unit_type']] ?? $unit['unit_type'];
                    ?> | 
                    수용: <?php echo $unit['capacity'] ?>명
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        
        <div class="info-panel" id="info-panel" style="display:none;">
            <h3>선택된 유닛</h3>
            <div class="field"><label>유닛 ID:</label> <span id="info-unit-id"></span></div>
            <div class="field"><label>이름:</label> <span id="info-name"></span></div>
            <div class="field"><label>X:</label> <span id="info-x"></span></div>
            <div class="field"><label>Y:</label> <span id="info-y"></span></div>
            <div class="field"><label>가로:</label> <span id="info-width"></span></div>
            <div class="field"><label>세로:</label> <span id="info-height"></span></div>
            <div class="field"><label>회전:</label> <span id="info-rotation"></span>°</div>
        </div>
        
        <div class="buttons">
            <button type="button" id="btn-save" class="btn">저장</button>
            <button type="button" id="btn-close" class="btn btn-secondary">닫기</button>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/konva@9/konva.min.js"></script>
<script>
const groupId = <?php echo $group_id ?>;
const canvasWidth = <?php echo $canvas_width ?>;
const canvasHeight = <?php echo $canvas_height ?>;
const backgroundImgUrl = <?php echo json_encode($background_img_url) ?>;
const unitsData = <?php echo json_encode($units) ?>;

// Konva Stage 생성
const stage = new Konva.Stage({
    container: 'container',
    width: canvasWidth,
    height: canvasHeight
});

const layer = new Konva.Layer();
stage.add(layer);

let selectedShape = null;
const shapes = {}; // unit_id -> shape 매핑

// 하나의 Transformer를 공유 (성능 최적화)
const transformer = new Konva.Transformer({
    keepRatio: false,
    enabledAnchors: ['top-left', 'top-right', 'bottom-left', 'bottom-right'],
    rotateEnabled: true,
    rotateAnchorOffset: 30, // 회전 앵커를 박스에서 더 멀리
    rotationSnaps: [0, 45, 90, 135, 180, 225, 270, 315], // 45도 단위로 스냅
    rotationSnapTolerance: 5, // 스냅 허용 오차
    anchorSize: 10, // 앵커 크기
    anchorStroke: '#0066cc', // 앵커 테두리 색
    anchorFill: '#ffffff', // 앵커 배경색
    anchorStrokeWidth: 2,
    borderStroke: '#0066cc', // 테두리 색
    borderStrokeWidth: 2,
    borderDash: [4, 4], // 점선 테두리
    rotateAnchorCursor: 'url("data:image/svg+xml;utf8,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'24\' height=\'24\' viewBox=\'0 0 24 24\'><path fill=\'%230066cc\' d=\'M12,6V9L16,5L12,1V4A8,8 0 0,0 4,12C4,13.57 4.46,15.03 5.24,16.26L6.7,14.8C6.25,13.97 6,13 6,12A6,6 0 0,1 12,6M18.76,7.74L17.3,9.2C17.74,10.04 18,11 18,12A6,6 0 0,1 12,18V15L8,19L12,23V20A8,8 0 0,0 20,12C20,10.43 19.54,8.97 18.76,7.74Z\'/></svg>") 12 12, auto',
    boundBoxFunc: function(oldBox, newBox) {
        // 최소 크기 제한 (20x20)
        if (Math.abs(newBox.width) < 20 || Math.abs(newBox.height) < 20) {
            return oldBox;
        }
        // 최대 크기 제한 (캔버스 크기)
        if (Math.abs(newBox.width) > canvasWidth || Math.abs(newBox.height) > canvasHeight) {
            return oldBox;
        }
        return newBox;
    }
});
layer.add(transformer);

// Transformer 이벤트 리스너
transformer.on('transformend', function() {
    const nodes = transformer.nodes();
    if (nodes.length === 0) return;

    const node = nodes[0];
    const scaleX = node.scaleX();
    const scaleY = node.scaleY();

    const rect = node.findOne('Rect');
    const text = node.findOne('Text');

    if (rect && text) {
        // 새 크기 계산
        const newWidth = Math.max(20, Math.abs(rect.width() * scaleX));
        const newHeight = Math.max(20, Math.abs(rect.height() * scaleY));

        // rect와 text 크기 업데이트
        rect.width(newWidth);
        rect.height(newHeight);
        text.width(newWidth);
        text.height(newHeight);

        // scale 초기화
        node.scaleX(1);
        node.scaleY(1);

        // position을 dragBoundFunc 제한 내로 조정
        const currentPos = {
            x: node.x(),
            y: node.y()
        };

        // dragBoundFunc와 동일한 로직 적용
        const minVisibleX = Math.min(newWidth * 0.4, 40);
        const minVisibleY = Math.min(newHeight * 0.4, 40);

        let newX = currentPos.x;
        let newY = currentPos.y;

        // X 좌표 제한 (최소 minVisibleX는 보여야 함)
        if (newX < minVisibleX - newWidth) {
            newX = minVisibleX - newWidth;
        }
        if (newX > canvasWidth - minVisibleX) {
            newX = canvasWidth - minVisibleX;
        }

        // Y 좌표 제한 (최소 minVisibleY는 보여야 함)
        if (newY < minVisibleY - newHeight) {
            newY = minVisibleY - newHeight;
        }
        if (newY > canvasHeight - minVisibleY) {
            newY = canvasHeight - minVisibleY;
        }

        // position 업데이트
        node.x(newX);
        node.y(newY);

        // 레이어 다시 그리기
        layer.draw();

        // 정보 패널 업데이트 (선택된 유닛 찾기)
        for (const unitId in shapes) {
            if (shapes[unitId].group === node) {
                updateInfo(node, shapes[unitId].unit);
                break;
            }
        }
    }
});

// 배경 이미지 로드
if (backgroundImgUrl) {
    const imageObj = new Image();
    imageObj.onload = function() {
        const bgImage = new Konva.Image({
            x: 0,
            y: 0,
            image: imageObj,
            width: canvasWidth,
            height: canvasHeight
        });
        layer.add(bgImage);
        bgImage.moveToBottom();
        layer.draw();
    };
    imageObj.src = backgroundImgUrl;
}

// 공간 유닛 렌더링
unitsData.forEach(unit => {
    // 좌표 검증 및 기본값 설정
    let x = parseFloat(unit.pos_x);
    let y = parseFloat(unit.pos_y);

    // 저장된 값이 유효하면 그대로 사용 (음수 및 캔버스 밖 포함)
    if (isNaN(x)) {
        x = Math.random() * (canvasWidth - 100);
    }
    if (isNaN(y)) {
        y = Math.random() * (canvasHeight - 80);
    }

    // 크기 검증 및 기본값 설정 (캔버스 크기 제한)
    let width = parseFloat(unit.width) || 100;
    let height = parseFloat(unit.height) || 80;

    if (isNaN(width) || width < 20) {
        width = 100;
    }
    if (width > canvasWidth) {
        width = Math.min(100, canvasWidth);
    }

    if (isNaN(height) || height < 20) {
        height = 80;
    }
    if (height > canvasHeight) {
        height = Math.min(80, canvasHeight);
    }

    // 회전 각도 검증 및 기본값 설정
    let rotation = parseFloat(unit.rotation_deg) || 0;
    if (isNaN(rotation) || rotation < -360 || rotation > 360) {
        rotation = 0;
    }
    
    const group = new Konva.Group({
        x: x,
        y: y,
        rotation: rotation,
        draggable: true,
        dragBoundFunc: function(pos) {
            const rect = this.findOne('Rect');
            if (!rect) return pos;

            const rectWidth = rect.width();
            const rectHeight = rect.height();

            // 유닛 크기의 40% 또는 최소 40px 중 작은 값을 항상 보이게 함
            const minVisibleX = Math.min(rectWidth * 0.4, 40);
            const minVisibleY = Math.min(rectHeight * 0.4, 40);

            let newX = pos.x;
            let newY = pos.y;

            // X 좌표 제한 (최소 minVisibleX는 보여야 함)
            if (newX < minVisibleX - rectWidth) {
                newX = minVisibleX - rectWidth;
            }
            if (newX > canvasWidth - minVisibleX) {
                newX = canvasWidth - minVisibleX;
            }

            // Y 좌표 제한 (최소 minVisibleY는 보여야 함)
            if (newY < minVisibleY - rectHeight) {
                newY = minVisibleY - rectHeight;
            }
            if (newY > canvasHeight - minVisibleY) {
                newY = canvasHeight - minVisibleY;
            }

            return {
                x: newX,
                y: newY
            };
        }
    });
    
    // 사각형
    const rect = new Konva.Rect({
        x: 0,
        y: 0,
        width: width,
        height: height,
        fill: getColorByType(unit.unit_type),
        stroke: '#333',
        strokeWidth: 2,
        opacity: 0.7
    });
    
    // 텍스트
    const text = new Konva.Text({
        x: 0,
        y: 0,
        width: width,
        height: height,
        text: unit.name,
        fontSize: 14,
        fontFamily: 'Malgun Gothic',
        fill: '#000',
        align: 'center',
        verticalAlign: 'middle',
        padding: 5
    });
    
    group.add(rect);
    group.add(text);
    layer.add(group);
    
    // 이벤트
    group.on('click tap', function(e) {
        selectShape(group, unit);
        e.cancelBubble = true;
    });
    
    // 드래그 시작 시 자동으로 선택
    group.on('dragstart', function(e) {
        selectShape(group, unit);
    });
    
    // 드래그 이동 완료 시 정보 업데이트
    group.on('dragend', function() {
        updateInfo(group, unit);
    });
    
    // Transform 시작 시에도 자동 선택 (크기 조절/회전)
    group.on('transformstart', function(e) {
        if (!selectedShape || selectedShape.unit.unit_id !== unit.unit_id) {
            selectShape(group, unit);
        }
    });
    
    shapes[unit.unit_id] = {
        group: group,
        rect: rect,
        text: text,
        unit: unit
    };
});

layer.draw();

// Stage 클릭 시 선택 해제 (빈 공간 클릭)
stage.on('click tap', function(e) {
    // 클릭한 대상이 Stage인 경우 (빈 공간)
    if (e.target === stage) {
        transformer.nodes([]);
        if (selectedShape) {
            selectedShape.rect.stroke('#333');
            selectedShape.rect.strokeWidth(2);
            selectedShape = null;
        }
        document.querySelectorAll('.unit-item').forEach(item => {
            item.classList.remove('selected');
        });
        document.getElementById('info-panel').style.display = 'none';
        layer.draw();
    }
});

function getColorByType(type) {
    const colors = {
        'ROOM': '#ffcccc',
        'TABLE': '#ccffcc',
        'SEAT': '#ccccff',
        'VIRTUAL': '#ffffcc'
    };
    return colors[type] || '#dddddd';
}

function selectShape(group, unit) {
    if (!group || !unit) {
        console.error('유효하지 않은 그룹 또는 유닛:', group, unit);
        return;
    }

    if (!unit.unit_id || isNaN(unit.unit_id) || unit.unit_id <= 0) {
        console.error('유효하지 않은 unit_id:', unit.unit_id);
        return;
    }

    // 이전 선택 해제
    if (selectedShape && selectedShape.rect) {
        selectedShape.rect.stroke('#333');
        selectedShape.rect.strokeWidth(2);
    }

    // 새로운 선택
    selectedShape = shapes[unit.unit_id];
    if (!selectedShape) {
        console.error('Shape를 찾을 수 없습니다:', unit.unit_id);
        return;
    }

    transformer.nodes([group]);
    if (selectedShape.rect) {
        selectedShape.rect.stroke('#0066cc');
        selectedShape.rect.strokeWidth(3);
    }

    layer.draw();

    // 사이드바 UI 업데이트
    document.querySelectorAll('.unit-item').forEach(item => {
        item.classList.remove('selected');
    });

    const selectedItem = document.querySelector(`.unit-item[data-unit-id="${unit.unit_id}"]`);
    if (selectedItem) {
        selectedItem.classList.add('selected');
    }

    updateInfo(group, unit);
}

function updateInfo(group, unit) {
    if (!group || !unit) {
        console.error('유효하지 않은 그룹 또는 유닛:', group, unit);
        return;
    }

    const infoPanel = document.getElementById('info-panel');
    if (!infoPanel) return;

    infoPanel.style.display = 'block';

    const rect = group.findOne('Rect');
    if (!rect) {
        console.error('Rect 요소를 찾을 수 없습니다.');
        return;
    }

    // 안전하게 값 추출 및 표시
    document.getElementById('info-unit-id').textContent = unit.unit_id || '-';
    document.getElementById('info-name').textContent = unit.name || '-';
    document.getElementById('info-x').textContent = Math.round((group.x() || 0) * 100) / 100;
    document.getElementById('info-y').textContent = Math.round((group.y() || 0) * 100) / 100;
    document.getElementById('info-width').textContent = Math.round((rect.width() || 0) * 100) / 100;
    document.getElementById('info-height').textContent = Math.round((rect.height() || 0) * 100) / 100;
    document.getElementById('info-rotation').textContent = Math.round((group.rotation() || 0) * 100) / 100;
}

// 사이드바 유닛 클릭
document.querySelectorAll('.unit-item').forEach(item => {
    item.addEventListener('click', function() {
        const unitId = parseInt(this.getAttribute('data-unit-id'));

        // unitId 검증
        if (isNaN(unitId) || unitId <= 0) {
            console.error('유효하지 않은 unit_id:', this.getAttribute('data-unit-id'));
            return;
        }

        const shapeData = shapes[unitId];
        if (shapeData) {
            selectShape(shapeData.group, shapeData.unit);

            // 캔버스 스크롤 조정
            const containerEl = document.getElementById('container');
            const groupPos = shapeData.group.getAbsolutePosition();
            containerEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
});

// 저장 버튼
document.getElementById('btn-save').addEventListener('click', async function() {
    const updates = [];

    for (const unitId in shapes) {
        const shapeData = shapes[unitId];
        const group = shapeData.group;
        const rect = shapeData.rect;

        // 값 추출 및 검증
        const unit_id = parseInt(unitId);
        let pos_x = Math.round(group.x() * 100) / 100;
        let pos_y = Math.round(group.y() * 100) / 100;
        let width = Math.round(rect.width() * 100) / 100;
        let height = Math.round(rect.height() * 100) / 100;
        let rotation_deg = Math.round(group.rotation() * 100) / 100;

        // 유효성 검증
        if (isNaN(unit_id) || unit_id <= 0) {
            console.error('유효하지 않은 unit_id:', unitId);
            continue;
        }

        // 좌표 범위 검증
        if (isNaN(pos_x) || pos_x < -10000 || pos_x > 10000) {
            console.error('유효하지 않은 pos_x:', pos_x);
            pos_x = 0;
        }
        if (isNaN(pos_y) || pos_y < -10000 || pos_y > 10000) {
            console.error('유효하지 않은 pos_y:', pos_y);
            pos_y = 0;
        }

        // 크기 범위 검증 (캔버스 크기 제한)
        if (isNaN(width) || width < 20 || width > canvasWidth) {
            console.error('유효하지 않은 width:', width);
            width = 100;
        }
        if (isNaN(height) || height < 20 || height > canvasHeight) {
            console.error('유효하지 않은 height:', height);
            height = 80;
        }

        // 회전 각도 범위 검증 (-360 ~ 360)
        if (isNaN(rotation_deg) || rotation_deg < -360 || rotation_deg > 360) {
            console.error('유효하지 않은 rotation_deg:', rotation_deg);
            rotation_deg = 0;
        }

        updates.push({
            unit_id: unit_id,
            pos_x: pos_x,
            pos_y: pos_y,
            width: width,
            height: height,
            rotation_deg: rotation_deg
        });
    }

    // 저장할 데이터가 없으면 종료
    if (updates.length === 0) {
        alert('저장할 공간 유닛이 없습니다.');
        return;
    }

    // groupId 검증
    if (isNaN(groupId) || groupId <= 0) {
        alert('유효하지 않은 공간 그룹 ID입니다.');
        return;
    }

    try {
        const response = await fetch('./ajax/shop_space_layout_update.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                group_id: groupId,
                units: updates
            })
        });

        const responseText = await response.text();

        let result;
        try {
            result = JSON.parse(responseText);
        } catch (parseError) {
            alert('서버 응답 파싱 오류\n\n응답 내용 (처음 500자):\n' + responseText.substring(0, 500));
            return;
        }

        if (result.success) {
            alert('저장되었습니다.\n' + (result.message || ''));
        } else {
            alert('저장 중 오류가 발생했습니다:\n' + (result.message || '알 수 없는 오류'));
        }
    } catch (error) {
        alert('저장 중 네트워크 오류가 발생했습니다:\n' + error.message);
    }
});

// 닫기 버튼
document.getElementById('btn-close').addEventListener('click', function() {
    if (confirm('변경사항을 저장하지 않고 닫으시겠습니까?')) {
        window.close();
    }
});
</script>

</body>
</html>

