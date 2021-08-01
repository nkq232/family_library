@extends('mainLayout')
@section('bannerContainer')
<section class="page-banner services-banner">
    <div class="container">
        <div class="banner-header">
            <h2>Books & Media Listing</h2>
            <span class="underline center"></span>
            <p class="lead">Proin ac eros pellentesque dolor pharetra tempo.</p>
        </div>
        <div class="breadcrumb">
            <ul>
                <li><a href="index-2.html">Home</a></li>
                <li>Books & Media</li>
            </ul>
        </div>
    </div>
</section>
@endsection
@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.1/css/all.min.css" />
<div id="content" class="site-content">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <div class="books-full-width">
                <div class="container">
                    <form>
                        <h3>Danh sách các loại sách</h3>
                        <div class="row">
                            <div class="col text-right">
                                <a type="button" class="btn btn-primary" href="{{ route('addBook') }}">Thêm sách</a>
                            </div>
                        </div>
                        <table class="table table-hover">
                            <thead>
                                <?php
                                $dc = "r";$bn = "r";$au = "r";$ge = "r";$pub = "r";$trans = "r";$coun = "r";$qua = "r";
                                $pri = "r";$insc = "r";$desc = "r";
                                ?>
                                <tr>
                                    <th>Số thứ tự/Order</th>
                                    <th>Mã DDC/DDC
                                    <a href="{{route('manageBooks', compact('dc','desc'))}}" id="dc_desc"><i class="fas fa-angle-double-down"></i></a>
                                        <a href="{{route('manageBooks', compact('dc','insc'))}}" id="dc_insc"><i class="fas fa-angle-double-up"></i></a></th>
                                    <th>Tên sách/Title
                                    <a href="{{route('manageBooks', compact('bn','desc'))}}" id="bn_desc"><i class="fas fa-angle-double-down"></i></a>
                                        <a href="{{route('manageBooks', compact('bn','insc'))}}" id="bn_insc"><i class="fas fa-angle-double-up"></i></a></th>
                                    <th>Tác giả/Author
                                    <a href="{{route('manageBooks', compact('au','desc'))}}" id="au_desc"><i class="fas fa-angle-double-down"></i></a>
                                        <a href="{{route('manageBooks', compact('au','insc'))}}" id="au_insc"><i class="fas fa-angle-double-up"></i></a></th>
                                    <th>Thể loại/Genre
                                    <a href="{{route('manageBooks', compact('ge','desc'))}}" id="ge_desc"><i class="fas fa-angle-double-down"></i></a>
                                        <a href="{{route('manageBooks', compact('ge','insc'))}}" id="ge_insc"><i class="fas fa-angle-double-up"></i></a></th>
                                    <th>Nhà sản xuất/Publisher
                                    <a href="{{route('manageBooks', compact('pub','desc'))}}" id="pub_desc"><i class="fas fa-angle-double-down"></i></a>
                                        <a href="{{route('manageBooks', compact('pub','insc'))}}" id="pub_insc"><i class="fas fa-angle-double-up"></i></a></th>
                                    <th>Người dịch/Translator
                                    <a href="{{route('manageBooks', compact('trans','desc'))}}" id="trans_desc"><i class="fas fa-angle-double-down"></i></a>
                                        <a href="{{route('manageBooks', compact('trans','insc'))}}" id="trans_insc"><i class="fas fa-angle-double-up"></i></a></th>
                                    <th>Quốc gia/Country
                                    <a href="{{route('manageBooks', compact('coun','desc'))}}" id="coun_desc"><i class="fas fa-angle-double-down"></i></a>
                                        <a href="{{route('manageBooks', compact('coun','insc'))}}" id="coun_insc"><i class="fas fa-angle-double-up"></i></a></th>
                                    <th>Số sách trong kho/Copies
                                    <a href="{{route('manageBooks', compact('qua','desc'))}}" id="qua_desc"><i class="fas fa-angle-double-down"></i></a>
                                        <a href="{{route('manageBooks', compact('qua','insc'))}}" id="qua_insc"><i class="fas fa-angle-double-up"></i></a></th>
                                    <th>Giá/Price.
                                    <a href="{{route('manageBooks', compact('pri','desc'))}}" id="pri_desc"><i class="fas fa-angle-double-down"></i></a>
                                        <a href="{{route('manageBooks', compact('pri','insc'))}}" id="pri_insc"><i class="fas fa-angle-double-up"></i></a></th>
                                    <th>Ảnh/Image</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $i = 0;
                                @endphp
                                @foreach ($books as $book)
                                
                                        @php
                                            $i++;
                                        @endphp
                                    <tr>
                                        <input type="hidden" class="deleteIdButton" value="{{ $book->id }}"/>
                                        <td>{{ $i }}</td>
                                        <td>{{ $book->ddcCode }}</td>
                                        <td>{{ $book->name }}</td>
                                        <td>{{ $book->author }}</td>
                                        <td>{{ $book->genre }}</td>
                                        <td>{{ $book->publisher }}</td>
                                        <td>{{ $book->translator }}</td>
                                        <td>{{ $book->country }}</td>
                                        <td>{{ $book->copies }}</td>
                                        <td>{{ $book->price }}</td>
                                        <td>
                                            <img src="/storage/{{ $book->image }}" alt="image"/>
                                        </td>
                                        <td>
                                            <?php $id = $book->id; ?>
                                            <a href="{{ route('editBook', compact('id')) }}" id="editButton" >
                                                <i class="fa fa-pen" aria-hidden="true" style="color: blue"></i>
                                            </a>
                                            <a rel="{{ $id }}" rel1= "book" href="javascript:" id="deleteButton" class="deleteButton">
                                                <i class="fa fa-trash" aria-hidden="true" style="color: red"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                
                            </tbody>
                
                        </table>
                    </form>
                </div>
                <!--navigation-->
                @include('includes.navigation', ['data'=>$books])
                <!--end navigation-->
            </div>
        </main>
    </div>
</div>
@endsection

@section('script')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/sweetalert2@11.0.19/dist/sweetalert2.all.min.js"></script>
<script>
    $(document).ready(function() {
        $url = window.location.href;
        if ($url == "http://127.0.0.1:8000/manageBooks?dc=r&desc=r") {
            document.getElementById("dc_desc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?dc=r&insc=r") {
            document.getElementById("dc_insc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?bn=r&desc=r") {
            document.getElementById("bn_desc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?bn=r&insc=r") {
            document.getElementById("bn_insc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?au=r&desc=r") {
            document.getElementById("au_desc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?au=r&insc=r") {
            document.getElementById("au_insc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?ge=r&desc=r") {
            document.getElementById("ge_desc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?ge=r&insc=r") {
            document.getElementById("ge_insc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?pub=r&desc=r") {
            document.getElementById("pub_desc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?pub=r&insc=r") {
            document.getElementById("pub_insc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?trans=r&desc=r") {
            document.getElementById("trans_desc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?trans=r&insc=r") {
            document.getElementById("trans_insc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?coun=r&desc=r") {
            document.getElementById("coun_desc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?coun=r&insc=r") {
            document.getElementById("coun_insc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?qua=r&desc=r") {
            document.getElementById("qua_desc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?qua=r&insc=r") {
            document.getElementById("qua_insc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?pri=r&desc=r") {
            document.getElementById("pri_desc").style.display = 'none';
        }
        if ($url == "http://127.0.0.1:8000/manageBooks?pri=r&insc=r") {
            document.getElementById("pri_insc").style.display = 'none';
        }
       $('a[id=deleteButton]').click(function() {
           var id = $(this).attr('rel');
           var deleteFunction = $(this).attr('rel1');
           swal({
            title: "Are you sure?",
            text: "Your will not be able to recover this imaginary file!",
            type: "warning",
            showCancelButton: true,
            confirmButtonClass: "btn-danger",
            confirmButtonText: "Yes, delete it!",
            closeOnConfirm: false
            },
            function(){
                swal("Deleted!", "Your imaginary file has been deleted.", "success");
                window.location.href = "/deleteBook/"+ deleteFunction + "/" + id;
            });
        })
    })
</script>

@endsection