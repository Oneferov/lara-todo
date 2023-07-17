@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form action="{{route('item.store')}}" method="POST" class="mb-3" id="form">
                @csrf
                <div class="mb-3">
                    <textarea name="text" class="form-control" placeholder="Введите текст"></textarea>
                </div>
                <div class="mb-3">
                    <input name="tag" class="form-control" type="text" placeholder="Введите теги">
                </div>
                <div class="input-group mb-3">
                    <input name="image" type="file" accept="image/*" class="form-control" id="image">
                </div>
                <img id="image-preview" src=""/>
                <button type="submit" class="btn btn-primary" id="submit">Сохранить новый элемент списка</button>
            </form>
            <div class="modal-alert-danger">
                <ul></ul>
            </div>
            <div class="card">
                <div class="card-header">List</div>
                <div class="card-body">
                    <ul class="list-group">
                        @foreach ($items as $item)
                            <li data-item_id="{{$item->id}}" class="list-group-item">
                                <div class="card-left">
                                    <textarea class="form-control item-text">{{$item->text}}</textarea>
                                    <div class="tags">
                                        <span onclick="createTag(event)" class="tag-add">&#8853;</span>
                                        @foreach ($item->tags as $tag)
                                            <span class="tag">
                                                <span>{{$tag->title}}</span>
                                                <span onclick="destroyTag(event, {{$tag->id}})" class="tag-remove">&#10008;</span>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="card-right">
                                    @if ($item->image)
                                        <div class="image-block">
                                            <img src="{{asset('storage/'.$item->image->path)}}" alt="">
                                            <span onclick="destroyImage(event)">&#10008;</span>
                                        </div>
                                    @else
                                        <div class="upload-image">
                                            <a class="upload-link js-fileapi-wrapper">
                                                <span class="upload-link__txt">Выберите изображение</span>
                                                <form method="PUT">
                                                    <input class="upload-link__inp item_image" name="item_image" type="file" accept="image/*"/>
                                                    @csrf
                                                </form>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                                <span class="destroy-item" onclick="destroyItem(event)">&#10008;</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var errorMessage = 'Повторите попытку позднее';
    $(document).ready(function() {
        $('#form').submit(function(event) {
            event.preventDefault(); 
            storeItem(this);
        });

        $('#image').change(function () {
            showPreview($(this)[0]);
        });

        $('.item-text').blur(function() {
            let field = $(this),
                itemId = field.parents('li').data('item_id');

            hideAlertDanger();

            if (field.val()) {
                editItem({text: field.val()}, itemId)
            } else {
                showAlertDanger('Поле не может быть пустым');
                showItem(itemId);
            }
        });

        // tagSroreListener();
        itemImageChangeListener();
    });

    function tagSroreListener(){
        $('.input-tag').blur(function() {
            let field = $(this),
                itemId = field.parents('li').data('item_id');

            field.attr('autofocus', true);
            // hideAlertDanger();

            if (field.val()) {
                storeTag({title: field.val()}, itemId)
            } 

            field.parent().remove();
                // showAlertDanger('Поле не может быть пустым');
                // showItem(itemId);
        });
    }

    function storeTag(data, itemId) {
        $.ajax({
            url: 'item/'+itemId+'/tag',
            type: 'POST',
            data: Object.assign(data, {"_token": "{{ csrf_token() }}"}),
            success: function (data)
            {
                if (data.success) {
                    let listItem = $(`li[data-item_id=${itemId}]`),
                        inputTag = listItem.find('.input-tag');
                    // inputTag.remove();

                    $.each(data.tags, function(idx, tag) {
                        console.log('one');
                        listItem.find('.tag-add').after(`
                            <span class="tag">
                                <span>${tag.title}</span>
                                <span onclick="destroyTag(event, ${tag.id})" class="tag-remove">&#10008;</span>
                            </span>
                        `);
                    })
                } else {
                    showAlertDanger(errorMessage);
                }
            },
            error: function (xhr, desc, err)
            {
                showAlertDanger(errorMessage);
            }
        }); 
    }

    function itemImageChangeListener() {
        $('.item_image').change(function () {
            editImage(this)
        });
    }

    function createTag(event)
    {
        let button = $(event.target);
        button.after(`
            <span class="tag">
                <input name="tag" class="input-tag" autofocus/>    
            </span>
        `);

        tagSroreListener();
    }

    function storeItem(node) {
        hideAlertDanger();
        $.ajax({
            url: $(node).attr("action"),
            type: $(node).attr("method"),
            dataType: "JSON",
            data: new FormData(node),
            processData: false,
            contentType: false,
            success: function (data)
            {
                if (data.model) {
                    let tagsHtml = '';

                    $.each(data.tags, function (idx, tag) {
                        tagsHtml += `
                            <span class="tag">
                                <span>${tag.title}</span>
                                <span onclick="destroyTag(event, ${tag.id})" class="tag-remove">&#10008;</span>
                            </span>
                        `;
                    })


                    let imageHtml = data.model.image 
                        ? `
                            <div class="image-block">
                                <img src="{{asset('storage/${data.model.image.path}')}}" alt="">
                                <span onclick="destroyImage(event)">&#10008;</span>
                            </div>
                        ` 
                        : `
                            <div class="upload-image">
                                <a class="upload-link js-fileapi-wrapper">
                                    <span class="upload-link__txt">Выберите изображение</span>
                                    <form method="PUT">
                                        <input class="upload-link__inp item_image" name="item_image" type="file" accept="image/*"/>
                                        @csrf
                                    </form>
                                </a>
                            </div>
                        `;

                    $('.list-group').prepend(`
                        <li data-item_id="${data.model.id}" class="list-group-item">
                            <div class="card-left">
                                <textarea class="form-control item-text">${data.model.text}</textarea>
                                <div class="tags">
                                    <span onclick="createTag(event)" class="tag-add">&#8853;</span>
                                    ${tagsHtml}
                                </div>
                            </div>
                            <div class="card-right">
                                ${imageHtml}
                            </div>
                            <span class="destroy-item" onclick="destroyItem(event)">&#10008;</span>
                        </li>
                    `);

                    hidePreview();
                    itemImageChangeListener();
                }
            },
            error: function (xhr, desc, err)
            {
                if (xhr.status == 422) {
                    Object.values(xhr.responseJSON.data).forEach(error => {
                        for (const [key, value] of Object.entries(error)) {
                            showAlertDanger(value)
                        }
                    })
                }
            }
        }); 
    }

    function showItem(itemId) {
        $.ajax({
            url: 'item/'+itemId,
            type: 'GET',
            success: function (data)
            {
                let model = data.model;
                if (model) {
                    $(`li[data-item_id=${model.id}]`).find('textarea').val(model.text);
                } else {
                    showAlertDanger(errorMessage);
                }
            },
            error: function (xhr, desc, err)
            {
                showAlertDanger(errorMessage);
            }
        }); 
    }

    function editItem(data, itemId) {
        $.ajax({
            url: 'item/'+itemId,
            type: 'PUT',
            data: Object.assign(data, {"_token": "{{ csrf_token() }}"}),
            success: function (data)
            {
                if (data.success) {
                    console.log('save ok');
                } else {
                    showAlertDanger(errorMessage);
                }
            },
            error: function (xhr, desc, err)
            {
                showAlertDanger(errorMessage);
            }
        }); 
    }

    function editImage(node) {
        let itemId = $(node).parents('li').data('item_id'),
                form = $(node).parents('form')[0],
                data = new FormData(form);    

        $.ajax({
            url: 'item/'+itemId+'/image',
            type: 'POST',
            data: data,
            processData: false,
            contentType: false,
            success: function (data)
            {
                if (data.success) {
                    let listItem = $(`li[data-item_id=${itemId}]`);
                    listItem.find('.upload-image').remove()
                    listItem.find('.card-right').append(`
                        <div class="image-block">
                            <img src="{{asset('storage/${data.image.path}')}}" alt="">
                            <span onclick="destroyImage(event)">&#10008;</span>
                        </div>
                    `);
                } else {
                    showAlertDanger(errorMessage);
                }
            },
            error: function (xhr, desc, err)
            {
                showAlertDanger(errorMessage);
            }
        }); 
    }

    function showPreview(input) {
        if (input.files && input.files[0]) {
            if (input.files[0].type.match('image.*')) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    $('#image-preview').show().attr('src', e.target.result).css('width', '150px').css('height', '150px');
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                showAlertDanger('Выбранный файл не является изображением');
            }
        } else {
            showAlertDanger(errorMessage);
        }
    }
   
    function hidePreview() {
        $('#image').val('');
        $('#image-preview').hide()
    }

    function destroyItem(event) {
        hideAlertDanger();
        let button = $(event.target);
        let itemId = button.parents('li').data('item_id');

        $.ajax({
            url: 'item/'+itemId,
            type: 'DELETE',
            data: {
                "_token": "{{ csrf_token() }}"
            },
            success: function (data)
            {
                if (data.success) {
                   button.parents('li').remove()
                } else {
                    showAlertDanger(errorMessage);
                }
            },
            error: function (xhr, desc, err)
            {
                showAlertDanger(errorMessage);
            }
        }); 
    }

    function destroyImage(event) {
        $('.item_image').off();
        hideAlertDanger();
        let button = $(event.target);
        let itemId = button.parents('li').data('item_id');

        $.ajax({
            url: 'item/'+itemId+'/image',
            type: 'DELETE',
            data: {
                "_token": "{{ csrf_token() }}"
            },
            success: function (data)
            {
                if (data.success) {
                    let imageBlock = button.parent('.image-block'),
                        li = button.parents('li');

                    imageBlock.remove();

                    li.find('.card-right').append(`
                        <div class="upload-image">
                            <a class="upload-link js-fileapi-wrapper">
                                <span class="upload-link__txt">Выберите изображение</span>
                                <form>
                                    <input class="upload-link__inp item_image" name="item_image" type="file" accept="image/*"/>
                                    @csrf
                                </form>
                            </a>
                        </div>
                    `);

                    itemImageChangeListener();
                } else {
                    showAlertDanger(errorMessage);
                }
            },
            error: function (xhr, desc, err)
            {
                showAlertDanger(errorMessage);
            }
        }); 
    }

    function destroyTag(event, tagId) {
        hideAlertDanger();
        let button = $(event.target);
        let itemId = button.parents('li').data('item_id');

        $.ajax({
            url: 'item/'+itemId+'/tag/'+tagId,
            type: 'DELETE',
            data: {
                "_token": "{{ csrf_token() }}"
            },
            success: function (data)
            {
                if (data.success) {
                    button.parent('.tag').remove();
                } else {
                    showAlertDanger(errorMessage);
                }
            },
            error: function (xhr, desc, err)
            {
                showAlertDanger(errorMessage);
            }
        }); 
    }

    function showAlertDanger(text) {
        $('.modal-alert-danger ul').append(`<li>${text}</li>`)
    }

    function hideAlertDanger() {
        $('.modal-alert-danger ul').empty();
    }
</script>

<style>
    .list-group-item {
        position: relative;
        display: flex;
        justify-content: space-between;
        padding-left: 2rem !important;

        border: 1px solid #5353f3 !important;
        border-radius: 10px;
        margin-bottom: 10px;

    }
    .list-group-item img {
        width: 150px;
        height: 150px;
    }
    .list-group-item .destroy-item {
        position: absolute;
        left: 0%;
        cursor: pointer;
        color: red;
        font-size: 2rem;
    }
    .modal-alert-danger {
        color: red;
    }
    .action {
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    .action button {
        display: block;
    }
    .card-right {
        display: flex;
        gap: 1rem;
    }
    .image-block {
        position: relative;
    }
    .image-block span {
        position: absolute;
        left: 80%;
        cursor: pointer;
        color: red;
        font-size: 2rem;
    }
    #image-preview {
        display: none;
    }
    .upload-image {
        width: 150px;
        height: 150px;
    }
    .upload-link {
        color: #36c;
        display: inline-block;
        overflow: hidden;
        position: relative;
        text-decoration: none;
        text-align: center;
        transform: translateY(100%);
    }
    .upload-link__inp {
        top: -10px;
        right: -40px;
        z-index: 2;
        position: absolute;
        cursor: pointer;
        opacity: 0;
        filter: alpha(opacity=0);
        font-size: 50px;
    }
    .tags .tag {
        border: 1px solid #8f520a;
        border-radius: 10px;
        padding: 0 5px 0 10px;
        background: #ffd200;
        color: #8f520a;
    }
    .tag-remove {
        font-weight: bold;
        margin-left: 5px;
        color: #bb0808;
        cursor: pointer;
    }
    .tag-add {
        font-size: 22px;
        font-weight: bold;
        color:green;
        cursor: pointer;
    }
    .tag {
        display: inline-block;
        margin: 0 1px;
    }
    .card-left {
        width: 100%;
    }
    .input-tag:focus {
        outline: none;
    }
    .input-tag {
        background: #ffd200;
        border: none;
        width: 70px;
    }
</style>
@endsection
