<?php
function smarty_block_ajax_form($params, $content, &$smarty, &$repeat)
{
  if ($content !== null)
  {
    $url = isset($params['url']) ? $params['url'] : $_SERVER['PHP_SELF'];
    $method = isset($params['method']) ? $params['method'] : 'get';
    $parameters = isset($params['params']) ? $params['params'] : '';
	$onsubmit = isset($params['onsubmit']) ? $params['onsubmit'] : '';

    return '<form method="' . $method . '" action="' . $url .
      '" onsubmit="' . $onsubmit . ';SmartyAjax.submit(this, \'' . $parameters . '\'' .
      (isset($params['callback']) ? ', ' . $params['callback'] : '') .
      '); return false;"' .
      (isset($params['id']) ? ' id="' . $params['id'] . '"' : '') .
      '>' . $content . '</form>';
  }
}

?>