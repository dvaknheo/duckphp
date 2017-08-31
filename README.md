# DNMVCS
## DNMVCS 是什么
一个 PHP Web 简单框架 比通常的Model Controller View 多了 Service
拟补了 常见 Web 框架少的缺层 
## DNMVCS 做了什么

## DNMVCS 不做什么
ORM ，和各种屏蔽 sql 的行为
模板引擎，PHP本身就是模板引擎
Widget ， 和 MVC 分离违背
系统行为 ，POST，GET 。


## DNMVCS 如何使用

## DNMVCS 的各个类说明
DNSingleton


DNAutoLoad
autoLoad
DNRoute
URL
_url
init
set404
run
defaltRouteHandle
addDefaultRoute
defaltDispathHandle
DNConfig
Setting
Get
Load
init
getSetting
getConfig
loadConfig
DNException
ThrowOn
DefaultHandel
HandelAllException
ManageException
SetMyHandel
OnException
DNView
Show
setBeforeShow
_show
showBlock
_assign
setWrapper
return_json
return_redirect
return_route_to
DNDB
init
check_connect
getPDO
close
quote
quote_array
fetchAll
fetch
fetchColumn
exec
rowCount
lastInsertId
get
insert
delete
update
DNMVCS
Service
Model
_load
onShow404
onException
onOtherException
onDebugError
onBeforeShow
init
run
isDev
onErrorHandler
CallAPI

## 还有什么要说的
