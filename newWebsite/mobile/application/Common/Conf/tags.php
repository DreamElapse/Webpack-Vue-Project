<?php
/**
 * 设置行为扩展
 */
return array(
    'app_begin' => array('Behavior\NewLangBehavior'),
    'view_filter' => array('Behavior\TokenBuildBehavior')
);