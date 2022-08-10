<?php
function smarty_function_ajax_update($params, &$smarty)
{
    $object = $params['object'];
    $update_id = isset($params['update_id']) ? $params['update_id'] : '';
    $function = isset($params['function']) ? $params['function'] : '';
    $url = isset($params['url']) ? $params['url'] : $_SERVER['PHP_SELF'];
    $method = isset($params['method']) ? $params['method'] : 'get';
    $parameters = isset($params['params']) ? $params['params'] : '';

    if ($parameters !== '') $parameters .= '&';
    $parameters .= 'f=' . $function;
    if (!empty($object)) $parameters .= '&o=' . $object;

    return 'SmartyAjax.update(\'' . $update_id . '\', \'' . $url .
    '\', \'' . $method . '\', \'' . $parameters . '\'); return false;';
}

?>