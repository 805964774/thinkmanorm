webman中使用的thinkorm没有日志，开启日志以后有bug，所以新建的这个项目，使用方法与webman相同，需要修改的内容如下
修改config/bootstrap.php中的Webman\ThinkOrm\ThinkOrm::class,为\Chengyi\Thinkmanorm\ThinkOrm::class