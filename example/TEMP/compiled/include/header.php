<?php defined('IN_IA') or exit('Access Denied');?><!doctype html>
<html ng-app="myApp">
<head>
<meta charset="utf-8">
<title>首页</title>
<meta content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no" id="viewport" name="viewport">
<meta name="format-detection" content="telephone=no" />
<?php  echo Whilegit\View\Template::css('iconfont.css');?>
<style>
.header_top {height:44px; width:100%;  background:#f8f8f8;  border-bottom:1px solid #e3e3e3;}
.header_top .title {height:44px; width:auto;font-size:14px; line-height:44px; color:#666;position: relative;text-align: center;background: #fff}
.header_top .title .icon-chevron-left{position: absolute;left: 10px;top:0px}
</style>
</head>
<body >

<div class="header_top">
    <div class="title"><i class='iconfont icon-chevron-left' onclick='history.back()'></i> 微分商城</div>
</div>