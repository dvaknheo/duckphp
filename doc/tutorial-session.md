# 会话
## 相关类

`Helper/AppHelper`



## 开始
通常，你把你的 Session 放在一个单独的 SessionService 里。 这个 SessionService  有些特殊，调用 App 类的 Session 系列方法。以便于在不同环境中处理 不同的 session 。

 异常处理也最好弄 SessionException 。