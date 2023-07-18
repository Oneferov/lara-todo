@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center d-flex">
        <div class="col-md-8">
            <form action="{{route('item.store')}}" method="POST" class="mb-3" id="form">
                @csrf
                <div class="mb-3">
                    <textarea name="text" id="input_text" class="form-control" placeholder="Введите текст"></textarea>
                </div>
                <div class="mb-3">
                    <input name="tag" id="input_tag" class="form-control" type="text" placeholder="Введите теги">
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
        <div class="col-md-4">
            <input name="tag" id="tag-search" class="form-control search-tag" type="text" placeholder="Поиск по тегам">
            <div class="card">
                <div class="card-header">List</div>
                <div class="card-body">
                    <div class="tags all_tags">
                        @foreach (\App\Models\Tag::orderBy('id', 'DESC')->get() as $tag)
                            <span data-tag_id="{{$tag->id}}" class="tag">
                                <span onclick="chooseTag(event)">{{$tag->title}}</span>
                            </span>
                        @endforeach
                    </div>
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

        $('#tag-search').on('input keyup', function(e) {
            searchItems();
        });

        itemImageChangeListener();
    });

    function chooseTag(event) {
        let value = $('#tag-search').val();
        let addValue = $(event.target).text();

        if (value.includes(addValue)) return;
            
        let newValue = value + ' ' +  addValue;
        $('#tag-search').val(newValue)

        searchItems();
    }

    function clearInputs() {
        $('#input_text').val('');
        $('#input_tag').val('');
    }

    function searchItems() {
        let value = $('#tag-search').val();

        $.ajax({
            url: 'item/search',
            type: 'GET',
            data: {value},
            success: function (data)
            {
                $('.list-group').empty();

                $.each(data.items, function(idx, item) {
                    renderItem(item);
                })
            },
            error: function (xhr, desc, err)
            {
                showAlertDanger(errorMessage);
            }
        }); 
    }    

    function tagStoreListener() {
        $('.input-tag').blur(function() {
            let field = $(this),
                itemId = field.parents('li').data('item_id');

            field.attr('autofocus', true);

            if (field.val()) {
                storeTag({title: field.val()}, itemId)
            } 

            field.parent().remove();
        });
    }

    function itemImageChangeListener() {
        $('.item_image').change(function () {
            editImage(this)
        });
    }

    function createTag(event) {
        let button = $(event.target);
        button.after(`
            <span class="tag">
                <input name="tag" class="input-tag" autofocus/>    
            </span>
        `);

        tagStoreListener();
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

                    $.each(data.tags, function(idx, tag) {
                        listItem.find('.tag-add').after(renderItemsTag(tag));
                        renderNewTag(tag);
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
                    renderItem(data.model);
                    clearInputs();
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
                    listItem.find('.card-right').append(renderImageBlock(data.image.path));
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
                    removeListTagItem(data.is_last);
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
                    removeListTagItem(data.is_last);
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

    function removeListTagItem(is_last) {
        if (!Object.keys(is_last).length) return;

        $.each(is_last, function(idx, tagId) {
            $(`span[data-tag_id=${tagId}]`).remove();
        })
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

    function showAlertDanger(text) {
        $('.modal-alert-danger ul').append(`<li>${text}</li>`)
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

    function hideAlertDanger() {
        $('.modal-alert-danger ul').empty();
    }

    function renderItemsTag(tag) {
        return `
            <span class="tag">
                <span>${tag.title}</span>
                <span onclick="destroyTag(event, ${tag.id})" class="tag-remove">&#10008;</span>
            </span>
        `
    }

    function renderImageBlock(path) {
        return `
            <div class="image-block">
                <img src="{{asset('storage/${path}')}}" alt="">
                <span onclick="destroyImage(event)">&#10008;</span>
            </div>
        `
    }

    function renderItem(item) {
        let tagsHtml = '';

        $.each(item.tags, function (idx, tag) {
            tagsHtml += renderItemsTag(tag);
            renderNewTag(tag)
        })

        let imageHtml = item.image 
            ? renderImageBlock(item.image.path) 
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
            <li data-item_id="${item.id}" class="list-group-item">
                <div class="card-left">
                    <textarea class="form-control item-text">${item.text}</textarea>
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
    }

    function renderNewTag(tag) {
        if ($(`span[data-tag_id=${tag.id}]`).length) return;

        $('.all_tags').append(`
            <span class="tag" data-tag_id="${tag.id}">
                <span onclick="chooseTag(event)">${tag.title}</span>
            </span>
        `);
    }
</script>
@endsection
