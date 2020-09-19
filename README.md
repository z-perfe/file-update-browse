laravel-admin file-update-browse
======

基于laravel-admin 框架， 表单的图片上传和浏览的JS插件。需求是图片等静态文件要求放在数据表中统一管理，
上传可以选择本地文件或浏览服务器中已有列表。

![表单页](https://github.com/zhpefe/file-update-browse/blob/master/image_0.JPG)


![弹出](https://github.com/zhpefe/file-update-browse/blob/master/image_1.JPG)

### 使用

* 下载 js 和 css 文件，并放置在 public 目录下
* 在 app/Admin/bootstrap.php 添加 
```php
Admin::css('/css/update_file_input.css');
Admin::js('/js/update_file_input.js');
```
* 创建文件数据表，例
```
CREATE TABLE `files` (
  `id` int(11) NOT NULL,
  `type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `path` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `url` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
```
* 创建所用的API，需要三个，地址可在update_file_input.js中自己修改：
```javascript
var upload_post_url = '/api/admin/upload_file'; // 上传
var upload_list_url = '/api/admin/file_list'; // 图片列表
var upload_post_ext_url = '/api/admin/add_ext_file'; // 外部地址的提交（可以存储在本地）
```
* 三个API的返回格式：

```php
function updateFile(Request $request){
    $files = $request->file('file');
    /*...*/
    return [["id"=> 'id', "url"=> "url"], ];
}
function fileList(Request $request){
    /*...*/
    return File::paginate(30);
}
function addExtFile(Request $request){
    /*...*/
    return ["id"=> 'id', "url"=> "url"];
}
```
* 在 laravel-admin 的 $form 中使用
```php
$attribute = [
    'class' => 'upload-file-input',     // 必须
    'style' => 'display:none',          // 必须
    'data-max_file_number' => '1',      // 图片数量
    'data-preview_sm' => '1',           // 是否使用小预览图
];
// 当前编辑ID通过request()->route()->parameter('路由参数')获取，可打印request()->route()查看。
$files = $form->isEditing() ? 
    $form->model()->find(request()->route()->parameter('路由参数'))->file()->get()->toArray() : 
    []; // belongsTo 关联
if (count($files)) {    
    // full_url 是 File 模型中$appends的字段，是文件地址的Storage::disk(config('admin.upload.disk'))->url($path) 
    $attribute['data-urls'] = implode(',', array_column($files,'full_url'));
}
$form->text('column', 'label')->attribute($attribute);
// 添加JS, PJAX真是让人头大，需要这样调用，否则页面可能需要刷新。
Admin::script(';update_init();');
```

* 或可以做成laravel-admin 扩展，因时间有限，只是临时解决方案。

