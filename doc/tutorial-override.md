# 接管替换默认实现

这些组件 都可以在 onInit 里通过类似方法替换
```php
Route::G(MyRoute::G());
View::G(MyView::G());
Configer::G(MyConfiger::G());
RuntimeState::G(MyRuntimeState::G());
```

例外的是 AutoLoader 和 ExceptionManager 。 这两个是在插件系统启动之前启动
所以你需要：
```php
AutoLoader::G()->clear();
AutoLoader::G(MyAutoLoader::G())->init($this->options,$this);

ExceptionManager::G()->clear();
ExceptionManager::G(MyExceptionManager::G())->init($this->options,$this);
```
如何替换组件。

注意的是核心组件都在 onInit 之前初始化了，所以你要自己初始化。
* 为什么核心组件都在 onInit 之前初始化。

为了 onInit 使用方便

* 为什么 Core 里面的都是 App::Foo(); 而 Ext 里面的都是 App::G()::Foo();
因为 Core 里的扩展都是在 DuckPHP\Core\App 下的。

Core 下面的扩展不会单独拿出来用， 
如果你扩展了该方面的类，最好也是让用户通过 App 或者 MVCS 组件来使用他们。


