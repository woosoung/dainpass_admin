<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

$dpt_sql = " WITH RECURSIVE TermPaths AS (
    -- Anchor
    SELECT
        trm_idx,
        trm_name,
        trm_name2,
        trm_name::TEXT AS path,
        trm_idx::TEXT AS idxs,
        trm_desc,
        trm_left,
        trm_right,
        0::BIGINT AS trm_depth,  -- 명시적으로 BIGINT로 변경
        trm_status
    FROM {$g5['term_table']}
    WHERE trm_idx_parent = 0
      AND trm_category = 'department'
      AND trm_status = 'ok'

    UNION ALL

    -- Recursive
    SELECT
        t.trm_idx,
        t.trm_name,
        t.trm_name2,
        (tp.path || ' > ' || t.trm_name)::TEXT AS path,
        (tp.idxs || ',' || t.trm_idx::TEXT)::TEXT AS idxs,
        t.trm_desc,
        t.trm_left,
        t.trm_right,
        (
            SELECT COUNT(*)::BIGINT  -- BIGINT로 명시
            FROM {$g5['term_table']} parent
            WHERE parent.trm_left < t.trm_left
              AND parent.trm_right > t.trm_right
              AND parent.trm_category = 'department'
              AND parent.trm_status = 'ok'
        ) AS trm_depth,
        t.trm_status
    FROM {$g5['term_table']} t
    JOIN TermPaths tp ON t.trm_idx_parent = tp.trm_idx
    WHERE t.trm_category = 'department'
      AND t.trm_status = 'ok'
      AND tp.trm_status = 'ok'
)
SELECT
    trm_idx, trm_name, trm_name2, path, idxs,
    trm_desc, trm_left, trm_right, trm_depth
FROM TermPaths
WHERE trm_status = 'ok'
ORDER BY trm_left;
";

$dpt_res = sql_query_pg($dpt_sql);

$department_arr = array();
$department_opt = '';
// if($dpt_res->num_rows > 0){
for($i=0;$row=sql_fetch_array_pg($dpt_res);$i++){
    $department_arr[$row['trm_idx']] = $row['trm_name'];
    $department_opt .= '<option value="'.$row['trm_idx'].'">'.$row['path'].'</option>';
}
// }
unset($dpt_sql);
unset($dpt_res);