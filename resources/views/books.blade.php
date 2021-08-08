@extends('mainLayout')
@section('bannerContainer')
<section class="page-banner services-banner">
    <div class="container">
        <div class="banner-header">
            <h2>Giá sách</h2>
            <span class="underline center"></span>
            <p class="lead">Lorem ipsum dolor sit amet, consectetur adipiscing elit.
                Pellentesque dolor turpis, pulvinar varius dui id, convallis iaculis eros.</p>
        </div>
        <div class="breadcrumb">
            <ul>
                <li><a href="index-2.html">Trang chủ</a></li>
                <li>Giá sách</li>
            </ul>
        </div>
    </div>
</section>
@endsection
@section('content')
<?php
$bn="r";$au="r";$pri="r";$gen="r";$coun="r";
?>
<div id="content" class="site-content">
    <div id="primary" class="content-area">
        <main id="main" class="site-main">
            <div class="books-full-width">
                <div class="container">
                    <!-- Start: Search Section -->
                    <section class="search-filters">
                        <div class="filter-box">
                            <h3>Bạn cần tìm gì?</h3>
                            <form action="{{route('bookSearch')}}" method="get">
                                <div class="col-md-4 col-sm-6" style="width:615px">
                                    <div class="form-group">
                                        <input class="form-control" placeholder="Từ khoá" id="keywords"
                                            name="keywords" type="text">
                                    </div>
                                </div>
                                <div class="col-md-3 col-sm-6">
                                    <div class="form-group">
                                        <select name="catalog" id="catalog" class="form-control">
                                            <option value="" disabled selected hidden>Tìm kiếm theo</option>
                                            <option value="ddcCode">DDC</option>
                                            <option value="name">Tiêu đề</option>
                                            <option value="author">Tác giả</option>
                                            <option value="publisher">Nhà xuất bản</option>
                                            <option value="translator">Người dịch</option>
                                            <option value="country">Quốc gia</option>
                                            <option value="genre">Thể loại</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2 col-sm-6">
                                    <div class="form-group">
                                        <input class="form-control" type="submit" value="Tìm kiếm">
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="clear"></div>
                    </section>
                    <!-- End: Search Section -->
                    <div class="filter-options margin-list">
                        <div class="row">
                            <div class="dropdown">
  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
    Sắp xếp theo
  </button>
  <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
    <li><a class="dropdown-item" href="{{ route('books',compact('bn')) }}">Tiêu đề</a></li>
    <li><a class="dropdown-item" href="{{ route('books',compact('au')) }}">Tác giả</a></li>
    <li><a class="dropdown-item" href="{{ route('books',compact('gen')) }}">Thể loại</a></li>
    <li><a class="dropdown-item" href="{{ route('books',compact('coun')) }}">Quốc gia</a></li>
  </ul>
</div>
                            
                            {{--<div class="col-md-2 col-sm-12 pull-right">
                                <div class="filter-toggle">
                                    <a href="books-media-gird-view-v2.html" class="active"><i
                                            class="glyphicon glyphicon-th-large"></i></a>
                                    <a href="books-media-list-view.html"><i class="glyphicon glyphicon-th-list"></i></a>
                                </div>
                            </div>--}}
                        </div>
                    </div>
                    <div class="booksmedia-fullwidth">
                        <ul>
                            @forelse ($books as $book)
                            <?php $id = $book->id; ?>
                            <li>
                                <figure>

                                    {{--  input  --}}
                                    <input type="hidden" id="base-url" value="{{ url('/') }}">
                                    <input type="hidden" name="_token" id="csrf" value="{{Session::token()}}">
                                    <input type="hidden" id="userId{{ $id }}" name="userId"
                                        value="{{ session('userId') }}">
                                    <input type="hidden" id="bookId{{ $id }}" name="bookId" value="{{ $id }}">



                                    {{-- end input --}}
                                    <a href="{{ route('book_detail_byId', compact('id')) }}"><img
                                            src="/storage/{{ $book->image }}" 
                                            alt="Book"></a>
                                    <figcaption>
                                        <header>
                                            <h4><a
                                                    href="{{ route('book_detail_byId', compact('id')) }}">{{ $book->name }}</a>
                                            </h4>
                                            <p><strong>Tác giả:</strong> {{ $book->author }}</p>
                                            <p><strong>DDC:</strong> {{ $book->ddcCode }}</p>
                                        </header>
                                        <p>(Nội dung chính)</p>
                                        <div class="actions">
                                            <ul>
                                                <li>
                                                    <a href="#" target="_blank" data-toggle="blog-tags"
                                                        data-placement="top" title="Add TO CART">
                                                        <i class="fa fa-shopping-cart"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    {{-- <a  data-toggle="blog-tags"
                                                        data-placement="top" title="Like" class="button_wishlist" id="{{ $id }}"
                                                    onclick="add_wistlist(this.id);">
                                                    <i class="fa fa-heart"></i>
                                                    </a> --}}
                                                    <a data-toggle="blog-tags" data-placement="top" title="Like"
                                                        class="button_wishlist" id="{{ $id }}"
                                                        onclick="saveFavorite(this.id);">
                                                        <i class="fa fa-heart {{ (!empty($book->favorite)) ? 'liked' : '' }}" id="heart{{ $id }}"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" target="_blank" data-toggle="blog-tags"
                                                        data-placement="top" title="Mail">
                                                        <i class="fa fa-envelope"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" target="_blank" data-toggle="blog-tags"
                                                        data-placement="top" title="Search">
                                                        <i class="fa fa-search"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" target="_blank" data-toggle="blog-tags"
                                                        data-placement="top" title="Print">
                                                        <i class="fa fa-print"></i>
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" target="_blank" data-toggle="blog-tags"
                                                        data-placement="top" title="Share">
                                                        <i class="fa fa-share-alt"></i>
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </figcaption>

                                </figure>
                            </li>
                            @empty
                            <div>Không có gì!</div>
                            @endforelse
                        </ul>
                    </div>
                    <!--navigation-->
                    @include('includes.navigation', ['data'=>$books])
                    <!--end navigation-->
                </div>
            </div>
        </main>
    </div>
</div>
@endsection