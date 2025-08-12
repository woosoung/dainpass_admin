<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$rank_sql = " WITH RECURSIVE TermPaths AS (
    -- Anchor member: 루트 노드를 가져옴
    SELECT
        trm_idx,
        trm_name,
        trm_name2,
        CAST(trm_name AS CHAR(255)) AS path,
        CAST(trm_idx AS CHAR(255)) AS idxs,
        trm_desc,
        trm_left,
        trm_right,
        0 AS trm_depth,
        trm_status
    FROM {$g5['term_table']}
    WHERE trm_idx_parent = 0  -- 루트 노드
        AND trm_category = 'rank'
        AND trm_status = 'ok'

    UNION ALL

    -- Recursive member: 하위 노드들을 경로와 함께 가져옴
    SELECT
        t.trm_idx,
        t.trm_name,
        t.trm_name2,
        CONCAT(tp.path, ' > ', t.trm_name) AS path,
        CONCAT(tp.idxs, ',', t.trm_idx) AS idxs,
        t.trm_desc,
        t.trm_left,
        t.trm_right,
        (SELECT COUNT(*)
         FROM {$g5['term_table']} parent
         WHERE parent.trm_left < t.trm_left
           AND parent.trm_right > t.trm_right) AS trm_depth,
        t.trm_status
    FROM {$g5['term_table']} t
    JOIN TermPaths tp ON t.trm_idx_parent = tp.trm_idx
    WHERE t.trm_category = 'rank'
      AND t.trm_status = 'ok'
      AND tp.trm_status = 'ok'
)
SELECT trm_idx, trm_name, trm_name2, path, idxs, trm_desc, trm_left, trm_right, trm_depth 
FROM TermPaths 
WHERE trm_status = 'ok'
ORDER BY trm_left;
";

$rank_res = sql_query($rank_sql,1);

$rank_arr = array();
$rank_rarr = array();
$rank_opt = '';
if($rank_res->num_rows > 0){
    for($i=0;$row=sql_fetch_array($rank_res);$i++){
        $rank_arr[$row['trm_idx']] = $row['trm_name'];
        $rank_rarr[$row['trm_name']] = $row['trm_idx'];
        $rank_opt .= '<option value="'.$row['trm_idx'].'">'.$row['path'].'</option>';
    }
}
unset($rank_sql);
unset($rank_res);