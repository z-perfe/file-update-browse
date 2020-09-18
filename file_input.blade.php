<script>
    $(function () {
        var upload_post_url = '/api/admin/upload';
        var upload_list_url = '/api/admin/file_list';
        var upload_post_ext_url = '/api/admin/add_ext_file';

        var type = {
            image : '图片',
            video : '视频',
            file : '文档'
        };
        var path = {
            goods: '商品相关',
            adv: '广告相关',
            shop: '店铺相关',
            page: '页面相关',
            icon: '图标',
            other: '其他'
        };
        var is_image_ext = ["jpg", "png", "gif", "jpeg"];
        var is_video_ext = ["mp4"];

        var type_option = '', path_option = '';
        Object.keys(type).forEach((i) => {
            type_option += '<option value="' + i + '">'+ type[i] +'</option>';
        });
        Object.keys(path).forEach((i) => {
            path_option += '<option value="' + i + '">'+ path[i] +'</option>';
        });
        var modal = `
        <div class="modal fade" id="image_modal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">上传</h4>
                </div>
                <div class="modal-body">
                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab_1" data-toggle="tab" aria-expanded="true">上传</a></li>
                            <li><a href="#tab_2" data-toggle="tab">浏览</a></li>
                            <li><a href="#tab_3" data-toggle="tab">文件地址</a></li>
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab_1">
                                <div class="upload_file_submit_div form-inline" style="display: none">
                                    <div class="input-group" id="select_file_list">
                                    </div>
                                    <div class="input-group ">
                                        <select name="type" id="select_type" class="form-control">
                                            `+ type_option +`
                                        </select>
                                    </div>
                                    <div class="input-group ">
                                        <select name="path" id="select_path" class="form-control">
                                            `+ path_option +`
                                        </select>
                                    </div>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-success" id="upload_submit">上传</button>
                                        <button type="button" class="btn btn-file" id="upload_cancel">取消</button>
                                    </div>
                                </div>
                                <div class="input-group select_file_button_div">
                                    <button type="button" class="btn btn-file" id="uploadButton">
                                        选择文件
                                    </button>
                                </div>
                                <input type="file" id="file" name="file[]" multiple="multiple"
                                       accept="image/*,.doc,.pdf,.xls,.xlsx" style="display: none">
                            </div>
                            <div class="tab-pane" id="tab_2">
                                <div class="form-inline">
                                    <div class="input-group ">
                                        <select name="type" id="select_show_type" class="form-control">
                                            `+ type_option +`
                                        </select>
                                    </div>
                                    <div class="input-group ">
                                        <select name="path" class="form-control" id="select_show_path">
                                            `+ path_option +`
                                        </select>
                                    </div>
                                    <div class="input-group pull-right">
                                        <button type="button" class="btn btn-success" id="browse_submit">确定选择</button>
                                    </div>
                                </div>
                                <div style="overflow-y: auto">
                                    <div id="image_list"></div>
                                    <nav aria-label="Page navigation">
                                        <ul class="pagination"></ul>
                                    </nav>
                                </div>
                            </div>
                            <div class="tab-pane" id="tab_3">
                                <div class="input-group">
                                    <span class="input-group-addon"><i class="fa fa-file-archive-o"></i></span>
                                    <input type="text" class="form-control" id="add_ext_file" placeholder="文件地址">
                                </div>
                                <div class="input-group" style="margin-top: 15px;">
                                    <button type="button" class="btn btn-success" id="add_ext_submit">确定添加</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
        </div>
        </div>
        `;
        $('body').append(modal);

        $('#uploadButton').on('click', function () {
            $('#file').click();
        });
        var current_column;
        $(document).on('click', '.add_image', function () {
            current_column = $(this).parent().prev();
            $('#image_modal').modal('show');
        });
        // 向主INPUT添加相关的键和图片地址
        var add_main_page = function (id, url, dom = null, update_input = true) {
            dom = dom || current_column;
            var val = dom.val();
            current_file_number = val ? val.split(',').length : 0;
            if (update_input && current_file_number >= dom.data('max_file_number')) {
                toastr.warning('文件数量超过上限');
                return false;
            }
            var li;
            if (/\.(png|jpe?g|gif)(\?.*)?(#.*)?$/.test(url)) {
                li = `<li class="image_bg"><img src="${url}" class="img" /><br />
                    <button type="button" class="btn btn-danger btn-xs delete_file" data-id="${id}">
                    <i class="fa fa-times"></i></button></li>`;
            } else {
                li = `<li class="file_bg">${url}<br />
                    <button type="button" class="btn btn-danger btn-xs delete_file" data-id="${id}">
                    <i class="fa fa-times"></i></button></li>`;
            }
            dom.next('ul').children('.add_image').before(li);
            if (update_input) {
                dom.val((index, old_val) => {
                    return old_val ? old_val + ',' + id : id;
                });
            }

        };
        $('.upload-file-input').each(function () {
            var _this = $(this);
            _this.siblings().hide();
            if (_this.data('preview_sm')) {
                _this.parent().parent().addClass('image_bg_sm');
            }
            _this.after(`
                <ul class="image_ul">
                    <li class="image_bg add_image">
                        <i class="fa fa-plus-circle"></i>
                    </li>
                </ul>`);
            if (_this.val() == 0) {
                _this.val('');
            }
            if (_this.val() && _this.data('urls')) {
                ids = _this.val().split(',');
                urls = _this.data('urls').split(',');
                urls.forEach(function(val, index) {
                    add_main_page(ids[index], val, _this, false);
                });
            }
        });
        var select_file_list = '';
        var is_image = (f) => {            ;
            var ext = f.substr(f.lastIndexOf(".") + 1);
            if (is_image_ext.indexOf(ext) > -1) {
                return true;
            }
            return false;
        };
        var is_video = (f) => {
            var ext = f.substr(f.lastIndexOf(".") + 1);
            if (is_video_ext.indexOf(ext) > -1) {
                return true;
            }
            return false;
        };
        $('#file').on('change', function (e) {
            var up_files = [];
            let files = e.target.files;
            if (!files.length) {
                return false;
            }
            for (let i = 0; i < files.length; i++) {
                up_files.push(files[i]);
            }
            $('#select_file_list').empty();
            if (is_image(up_files[0].name)) {
                $('#select_type').val('image');
                up_files.forEach((v) => {
                    $('#select_file_list').append($('<img />').attr({src: URL.createObjectURL(v)}));
                });
            } else {
                var name = [];
                up_files.forEach((v) => {
                    name.push(v.name);
                });
                select_file_list = '<input type="text" class="form-control" value="' + name.join(',') + '"/>';
                $('#select_file_list').append(select_file_list);
                $('#select_type').val(is_video(up_files[0].name) ? 'video' : 'file');
            }
            $('.select_file_button_div').hide();
            $('.upload_file_submit_div').show();
        });
        // 重新选择上传
        $('#upload_cancel').click(function () {
            $('#select_file_list').empty();
            $('.select_file_button_div').show();
            $('.upload_file_submit_div').hide();
        });
        // 上传
        $('#upload_submit').click(() => {
            var data = new FormData();
            var files = $("#file")[0].files;
            for (var i = 0; i < files.length; i++) {
                data.append("file[]", files[i]);
            }
            data.append('type', $('#select_type').val());
            data.append('path', $('#select_path').val());
            $.ajax({
                data: data,
                url: upload_post_url,
                type: "POST",
                dataType: "json",
                cache: false,
                contentType: false,
                processData: false,
                success: function (res) {
                    console.log(res);
                    if (res.status) {
                        res.data.forEach((v) => {
                            add_main_page(v.id, v.url);
                        });
                    } else {
                        toastr.error('上传错误');
                    }
                    $('#select_file_list').empty();
                    $('.select_file_button_div').show();
                    $('.upload_file_submit_div').hide();
                    $('#image_modal').modal('hide');
                }
            })
        });
        $(document).on('click', '.delete_file', function () {
            var id = $(this).data('id');
            var input = $(this).parent().parent().prev();
            input.val((index, old_val) => {
                var val = old_val.split(',');
                return val.filter((v) => v != id);
            });
            $(this).parent().remove();
        });

        var image_list = (page) => {
            var type = $('#select_show_type').val();
            var path = $('#select_show_path').val();
            $.get(upload_list_url,
                {type: type, path: path, page: page},
                function (res) {
                    if (!res.status) {
                        toastr('获取失败');
                    }
                    var data = res.data;
                    let images = data.data;
                    var image_li = '';
                    images.forEach((img) => {
                        image_li += '<a href="javascript:;" class="list_image" data-id="' + img.id + '">' +
                            '<img src="' + img.full_url + '" />' +
                            '</a>';
                    });
                    $('#image_list').empty().append(image_li);
                    // 分页
                    var current_page = data.current_page;
                    var last_page = data.last_page
                    var start_page = current_page - 2 > 1 ? current_page - 2 : 1;
                    var end_page = current_page + 5 > last_page ? last_page : current_page + 5;
                    var li = '';
                    if (current_page != 1) {
                        li += '<li><a href="javascript:;" aria-label="Previous" data-page="1">' +
                            '<span aria-hidden="true">&laquo;</span></a></li>';
                    }
                    if (start_page != 1) {
                        li += '<li><a href="javascript:;">..</a></li>';
                    }
                    for (var i = start_page; i <= end_page; i++) {
                        var active = i == current_page ? 'active' : '';
                        li += `<li class="${active}"><a href="javascript:;" data-page="${i}">${i}</a></li>`;
                    }
                    if (last_page > end_page) {
                        li += '<li><a href="javascript:;">..</a></li>';
                    }
                    if (last_page != end_page) {
                        li += '<li><a href="javascript:;" aria-label="Previous" data-page="' + last_page + '">' +
                            '<span aria-hidden="true">&raquo;</span></a></li>';
                    }
                    $('.pagination').empty().append(li);
                })
        };
        // 浏览文件的点击
        var browse_checked = {};
        image_list(1);
        $('#image_list').on('click', 'a', function () {
            var id = $(this).data('id');
            if ($(this).hasClass('active')) {
                $(this).removeClass('active');
                delete browse_checked[id];
            } else {
                $(this).addClass('active');
                browse_checked[id] = $(this).children('img').attr('src');
            }
        });
        // 两个浏览选项的更改
        $('#select_show_type').change(function () {
            image_list(1);
        });
        $('#select_show_path').change(function () {
            image_list(1);
        });
        // 图片列表点击
        $('.pagination').on('click', 'a[data-page]', function () {
            image_list($(this).data('page'));
        });
        $('#browse_submit').click(function () {
            Object.keys(browse_checked).forEach((k) => {
                add_main_page(k, browse_checked[k]);
            });
            browse_checked = {};
            $('#image_modal').modal('hide');
            $('#image_list a').removeClass('active');
        });
        // 添加外部链接
        $('#add_ext_submit').click(function () {
            var val = $('#add_ext_file').val();
            if (/http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/.test(val)) {
                $.post(
                    upload_post_ext_url,
                    {url: val},
                    function (res) {
                        if (res.status) {
                            add_main_page(res.data.id, res.data.url);
                        } else {
                            toastr.error('上传错误');
                        }
                        $('#add_ext_file').val('');
                        $('#image_modal').modal('hide');
                    }
                )
            }
        });
    });
</script>
