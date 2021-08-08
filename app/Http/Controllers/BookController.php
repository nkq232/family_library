<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Borrowing;
use Illuminate\Support\Facades\DB;


class BookController extends Controller
{
    public function index(Request $request) {
        if ($request->bn == "r") {
            $books = Book::orderBy('books.name','asc');
        } else if ($request->au == "r") {
            $books = Book::orderBy('books.author','asc');
        } else if ($request->pri == "r") {
            $books = Book::orderBy('books.price','asc');
        } else if ($request->gen == "r") {
            $books = Book::orderBy('books.genre','asc');
        } else if ($request->coun == "r") {
            $books = Book::orderBy('books.country','asc') ;
        }
        else {
        $books = Book::orderBy('books.id', 'asc');
        }
        
        
        if (!empty(session('userId'))){
            $books = $books->paginate(10, ["*", 
            DB::raw("(SELECT userId FROM wish_lists WHERE bookId = books.id AND userId = ".session('userId').") favorite")]);
        } else {
            $books = $books->paginate(10);
        }
        $active = "books";
        return view('books', compact('books', 'active'));
    }
    public function addBookForm() {
        $active = "books";
        return view('addBook', compact('active'));
    }
    public function addBookPost(Request $request) {
        $this->validate($request, [
			'name' => 'required',
			'price'=>'required|numeric',
            'copies'=>'numeric',
            ]
		);
        // kiểm tra có files sẽ xử lý
        $book = Book::create($request->all());
        if($request->hasFile('img')) {
			$allowedfileExtension=['jpg','png'];
			$file = $request->file('img');
			$exe_flg = true;
            $extension = $file->getClientOriginalExtension();
            $check=in_array($extension,$allowedfileExtension);

            if(!$check) {
                // nếu có file nào không đúng đuôi mở rộng thì đổi flag thành false
                $exe_flg = false;
            }
			// nếu không có file nào vi phạm validate thì tiến hành lưu DB
			if($exe_flg) {
                // lưu product
                $image = $request->img;
                $filename = $image->store('public');
                $filename = substr($filename, 6);
                $book->image = $filename;
                $book->save();
                return redirect(route('manageBooks'));
			} 
		}
        
        return view('manageBooks');
    }
    public function editBookForm(Request $request) {
        $active = "manage";
        $bookId = $request->id;
        $book = Book::where('id', $request->id)->get()->first();
        return view('editBook', compact('book', 'active'));
    }
    public function editBookPost(Request $request) {
        $this->validate($request, [
			'name' => 'required',
			'price'=>'required|numeric',
            'copies'=>'numeric',
            ]
		);
        $book = Book::where('id', $request->id)->first();
        $book->ddcCode = $request->ddcCode;
        $book->name = $request->name;
        $book->author = $request->author;
        $book->genre = $request->genre;
        $book->publisher = $request->publisher;
        $book->translator = $request->translator;
        $book->country = $request->country;
        $book->review = $request->review;
        $book->copies = $request->copies;
        $book->price = $request->price;
        $book->save();
        if($request->hasFile('img')) {
			$allowedfileExtension=['jpg','png'];
			$file = $request->file('img');
			$exe_flg = true;
            $extension = $file->getClientOriginalExtension();
            $check=in_array($extension,$allowedfileExtension);

            if(!$check) {
                // nếu có file nào không đúng đuôi mở rộng thì đổi flag thành false
                $exe_flg = false;
            }
			// nếu không có file nào vi phạm validate thì tiến hành lưu DB
			if($exe_flg) {
                // lưu product
                $image = $request->img;
                $filename = $image->store('public');
                $filename = substr($filename, 6);
                $book->image = $filename;
                $book->save();
                return redirect(route('manageBooks'));
			} 
		}
        return redirect(route('manageBooks'));
    }

    public function deleteBook(Request $request, $id) {
        // $book = Book::where('id', $request->id)->first();
        $book = Book::where('id', $id)->first();
        $book->delete();
        // return response()->json(['status'=> 'Deleted successfully']);
        // return route('manageBooks');
        return redirect()->back()->with("flash_message_success", "Delete book");
    }

    //for Admin
    public function showBookList(Request $request) {
        // if ($request->delete == 'yes') {
        //     $id = $request->id;
        //     return redirect(route('deleteBook', compact('id')));
        // }
        $active = "manage";
        $limit = 10;
        if ($request->dc == "r" && $request->insc == "r") {
            $books = DB::table('books')->orderBy('books.ddcCode','asc')->paginate($limit);
        }
        else if ($request->dc == "r" && $request->desc == "r") {
            $books = DB::table('books')->orderBy('books.ddcCode','desc')->paginate($limit);
        }
        else if ($request->bn == "r" && $request->insc == "r") {
            $books = DB::table('books')->orderBy('books.name','asc')->paginate($limit);
        }
        else if ($request->bn == "r" && $request->desc == "r") {
            $books = DB::table('books')->orderBy('books.name','desc')->paginate($limit);
        }
        else if ($request->au == "r" && $request->insc == "r") {
            $books = DB::table('books')->orderBy('books.author','asc')->paginate($limit);
        }
        else if ($request->au == "r" && $request->desc == "r") {
            $books = DB::table('books')->orderBy('books.author','desc')->paginate($limit);
        }
        else if ($request->ge == "r" && $request->insc == "r") {
            $books = DB::table('books')->orderBy('books.genre','asc')->paginate($limit);
        }
        else if ($request->ge == "r" && $request->desc == "r") {
            $books = DB::table('books')->orderBy('books.genre','desc')->paginate($limit);
        }
        else if ($request->pub == "r" && $request->insc == "r") {
            $books = DB::table('books')->orderBy('books.publisher','asc')->paginate($limit);
        }
        else if ($request->pub == "r" && $request->desc == "r") {
            $books = DB::table('books')->orderBy('books.publisher','desc')->paginate($limit);
        }
        else if ($request->trans == "r" && $request->insc == "r") {
            $books = DB::table('books')->orderBy('books.translator','asc')->paginate($limit);
        }
        else if ($request->trans == "r" && $request->desc == "r") {
            $books = DB::table('books')->orderBy('books.translator','desc')->paginate($limit);
        }
        else if ($request->coun == "r" && $request->insc == "r") {
            $books = DB::table('books')->orderBy('books.country','asc')->paginate($limit);
        }
        else if ($request->coun == "r" && $request->desc == "r") {
            $books = DB::table('books')->orderBy('books.country','desc')->paginate($limit);
        }
        else if ($request->qua == "r" && $request->insc == "r") {
            $books = DB::table('books')->orderBy('books.copies','asc')->paginate($limit);
        }
        else if ($request->qua == "r" && $request->desc == "r") {
            $books = DB::table('books')->orderBy('books.copies','desc')->paginate($limit);
        }
        else if ($request->pri == "r" && $request->insc == "r") {
            $books = DB::table('books')->orderBy('books.price','asc')->paginate($limit);
        }
        else if ($request->pri == "r" && $request->desc == "r") {
            $books = DB::table('books')->orderBy('books.price','desc')->paginate($limit);
        }
        else {
        $books = DB::table('books')->paginate($limit);
        }
        return view('manageBook', compact('books', 'active'));
    }
   
    public function showBorrowedBookList(Request $request) {
        $active = "check";
        $limit = 5;
        $id = session('userId');
        if ($request->dc == "r" && $request->insc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.ddcCode','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->dc == "r" && $request->desc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.ddcCode','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->bn == "r" && $request->insc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.name','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->bn == "r" && $request->desc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.name','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->au == "r" && $request->insc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.author','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->au == "r" && $request->desc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.author','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->ge == "r" && $request->insc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.genre','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->ge == "r" && $request->desc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.genre','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->qua == "r" && $request->insc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('borrowings.quantity','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->qua == "r" && $request->desc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('borrowings.quantity','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->bd == "r" && $request->insc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('borrowings.borrowDate','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->bd == "r" && $request->desc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('borrowings.borrowDate','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->rd == "r" && $request->insc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('borrowings.returnDate','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->rd == "r" && $request->desc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('borrowings.returnDate','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }  
        else {
        $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)

                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('borrowings.id', 'desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        return view('borrowedBookList', compact('books', 'active'));
    }

    public function showReturnedBookList(Request $request) {
        $active = "check";
        $limit = 5;
        $id = session('userId');
        if ($request->dc == "r" && $request->insc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned',"returned")
                            ->orderBy('books.ddcCode','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->dc == "r" && $request->desc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned',"returned")
                            ->orderBy('books.ddcCode','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->bn == "r" && $request->insc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('books.name','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->bn == "r" && $request->desc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('books.name','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->au == "r" && $request->insc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('books.author','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->au == "r" && $request->desc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned',"returned")
                            ->orderBy('books.author','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->ge == "r" && $request->insc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('books.genre','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->ge == "r" && $request->desc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('books.genre','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->qua == "r" && $request->insc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('borrowings.quantity','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->qua == "r" && $request->desc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('borrowings.quantity','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->bd == "r" && $request->insc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('borrowings.borrowDate','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->bd == "r" && $request->desc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('borrowings.borrowDate','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->rd == "r" && $request->insc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('borrowings.returnDate','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->rd == "r" && $request->desc == "r") {
            $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('borrowings.returnDate','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }  
        else {
        $books = DB::table('borrowings')->join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        $returned = true;
        return view('borrowedBookList', compact('books', 'returned', 'active'));
    }

    
}
