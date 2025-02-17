<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Borrowing;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\UserInfo;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Http\Controllers\WishListController;
use Cookie;

class BookController extends Controller
{
    public function index(Request $request)
    {
        if ($request->bn == "r") {
            $books = Book::orderBy('books.name', 'asc');
        } else if ($request->au == "r") {
            $books = Book::orderBy('books.author', 'asc');
        } else if ($request->pri == "r") {
            $books = Book::orderBy('books.price', 'asc');
        } else if ($request->gen == "r") {
            $books = Book::orderBy('books.genre', 'asc');
        } else if ($request->coun == "r") {
            $books = Book::orderBy('books.country', 'asc');
        } else {
            $books = Book::orderBy('books.id', 'asc');
        }

        if (!empty(session('userId'))) {
            $userId = session('userId');
            $books = $books
                ->select("'books'.*",
                        DB::raw("(SELECT 'wish_lists'.userId
                        FROM wish_lists
                       WHERE bookId = books.id AND userId = $userId) as favorite")
                )
                ->paginate(10);
        } else {
            $books = $books->paginate(10);
        }
        $active = "books";
        return view('books', compact('books', 'active'));
    }
    public function addBookForm()
    {
        $active = "books";
        $myfile = fopen("../public/assets/data/ddcCode.txt", "r");
        $ddc = array();
        while (!feof($myfile)) {
            $s = fgets($myfile);
            $num = substr($s, 0, 3);
            $classify = substr($s, 3);
            $ddc[$num] = $classify;
        }
        fclose($myfile);
        $level1 = array();
        $id = array("000", "100", "200", "300", "400", "500", "600", "700", "800", "900");
        foreach ($id as $i) {
            $level1[$i] = $ddc[$i];
        }
        return view('addBook', compact('active', 'level1'));
    }

    public function addBooklevel2(Request $request)
    {
        $value = $request->get('value');
        $myfile = fopen("../public/assets/data/ddcCode.txt", "r");
        $ddc = array();
        while (!feof($myfile)) {
            $s = fgets($myfile);
            $num = substr($s, 0, 3);
            $classify = substr($s, 3);
            $ddc[$num] = $classify;
        }
        fclose($myfile);

        $value = $value[0];
        if ($value != '0') {
            $id = array($value . "10", $value . "20", $value . "30", $value . "40", $value . "50", $value . "60", $value . "70", $value . "80", $value . "90");
        } else { //ddc value 040 undefined
            $id = array($value . "10", $value . "20", $value . "30", $value . "50", $value . "60", $value . "70", $value . "80", $value . "90");
        }
        $output = '<option value="">Chọn phân lớp</option>';
        foreach ($id as $i) {
            $output .= '<option value="' . $i . '">' . $ddc[$i] . '</option>';
        }
        echo $output;
    }
    public function addBookPost(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => 'required',
                'price' => 'required|numeric',
                'copies' => 'numeric',
            ]
        );
        // kiểm tra có files sẽ xử lý
        $book = new Book();
        $book->name = $request->name;
        $book->author = $request->author;
        $book->genre = $request->genre;
        $book->publisher = $request->publisher;
        $book->translator = $request->translator;
        $book->country = $request->country;
        $book->review = $request->review;
        $book->price = $request->price;
        $book->copies = 1;
        if (($request->level2 == "") && ($request->level1 == "")) {
            $book->ddcCode = "chưa chọn";
        } else if ($request->level2 == "") {
            $book->ddcCode = $request->level1;
        } else {
            $book->ddcCode = $request->level2;
        }
        $book->save();

        if ($request->hasFile('img')) {
            $allowedfileExtension = ['jpg', 'png'];
            $file = $request->file('img')->storeOnCloudinary();
            $exe_flg = true;
            $fileN = $request->file('img');
            $extension = $fileN->getClientOriginalExtension();
            $check = in_array($extension, $allowedfileExtension);

            if (!$check) {
                // nếu có file nào không đúng đuôi mở rộng thì đổi flag thành false
                $exe_flg = false;
            }
            // nếu không có file nào vi phạm validate thì tiến hành lưu DB
            if ($exe_flg) {
                // lưu product
                $image = $request->img;
                $filename = $image->store('photo');
                $filename = substr($filename, 6);
                $book->image = $file->getPath();
                $book->save();
                return redirect(route('manageBooks'));
            }
        }

        return view('manageBooks');
    }
    public function editBookForm(Request $request)
    {
        $active = "manage";
        $bookId = $request->id;
        $book = Book::where('id', $request->id)->get()->first();

        $myfile = fopen("../public/assets/data/ddcCode.txt", "r");
        $ddc = array();
        while (!feof($myfile)) {
            $s = fgets($myfile);
            $num = substr($s, 0, 3);
            $classify = substr($s, 3);
            $ddc[$num] = $classify;
        }
        fclose($myfile);

        $level1 = array();
        $level2 = array();
        $selected1 = "";
        $selected2 = "";

        $id = array("000", "100", "200", "300", "400", "500", "600", "700", "800", "900");
        foreach ($id as $i) {
            $level1[$i] = $ddc[$i];
        }
        if ($book->ddcCode != "chưa chọn") {
            $pre = $book->ddcCode[0];
            $selected1 = $pre.'00';
            
            if ($selected1 != $book->ddcCode) {
                $selected2 = $book->ddcCode;
            } else {
                $selected2 = "";
            }
            
            if ($pre != '0') {
                $id2 = array($pre."10", $pre."20", $pre."30", $pre."40", $pre."50", $pre."60", $pre."70", $pre."80", $pre."90");
            } else { //ddc value 040 undefined
                $id2 = array($pre."10", $pre."20", $pre."30", $pre."50", $pre."60", $pre."70", $pre."80", $pre."90");
            }   
            foreach($id2 as $i) {
                $level2[$i] = $ddc[$i];
            }
        }
        return view('editBook', compact('book', 'active', 'level1', 'level2', 'selected1', 'selected2'));
    }
    public function editBookPost(Request $request)
    {
        // $this->validate($request, [
        // 	'name' => 'required',
        // 	'price'=>'required|numeric',
        //     'copies'=>'numeric',
        //     ]
        // );
        $book = Book::where('id', $request->id)->first();
        $book->name = $request->name;
        $book->author = $request->author;
        $book->genre = $request->genre;
        $book->publisher = $request->publisher;
        $book->translator = $request->translator;
        $book->country = $request->country;
        $book->review = $request->review;
        $book->price = $request->price;
        if (($request->level2 == "") && ($request->level1 == "")) {
            $book->ddcCode = "chưa chọn";
        } else if ($request->level2 == "") {
            $book->ddcCode = $request->level1;
        } else {
            $book->ddcCode = $request->level2;
        }
        $book->save();
        if ($request->hasFile('img')) {
            $allowedfileExtension = ['jpg', 'png'];
            $file = $request->file('img')->storeOnCloudinary();
            $exe_flg = true;
            $fileN = $request->file('img');
            $extension = $fileN->getClientOriginalExtension();
            $check = in_array($extension, $allowedfileExtension);

            if (!$check) {
                // nếu có file nào không đúng đuôi mở rộng thì đổi flag thành false
                $exe_flg = false;
            }
            // nếu không có file nào vi phạm validate thì tiến hành lưu DB
            if ($exe_flg) {
                // lưu product
                $image = $request->img;
                $filename = $image->store('photo');
                $filename = substr($filename, 6);
                $book->image = $file->getPath();
                $book->save();
                return redirect(route('manageBooks'));
            }
        }
        return redirect(route('manageBooks'));
    }

    public function deleteBook(Request $request, $id)
    {
        // $book = Book::where('id', $request->id)->first();
        $book = Book::where('id', $id)->first();
        $book->delete();
        // return response()->json(['status'=> 'Deleted successfully']);
        // return route('manageBooks');
        return redirect()->back()->with("flash_message_success", "Delete book");
    }

    //for Admin
    public function showBookList(Request $request)
    {
        // if ($request->delete == 'yes') {
        //     $id = $request->id;
        //     return redirect(route('deleteBook', compact('id')));
        // }
        $active = "manage";
        $limit = 10;
        if ($request->dc == "r" && $request->insc == "r") {
            $books = Book::orderBy('books.ddcCode','asc')->paginate($limit);
        }
        else if ($request->dc == "r" && $request->desc == "r") {
            $books = Book::orderBy('books.ddcCode','desc')->paginate($limit);
        }
        else if ($request->bn == "r" && $request->insc == "r") {
            $books = Book::orderBy('books.name','asc')->paginate($limit);
        }
        else if ($request->bn == "r" && $request->desc == "r") {
            $books = Book::orderBy('books.name','desc')->paginate($limit);
        }
        else if ($request->au == "r" && $request->insc == "r") {
            $books = Book::orderBy('books.author','asc')->paginate($limit);
        }
        else if ($request->au == "r" && $request->desc == "r") {
            $books = Book::orderBy('books.author','desc')->paginate($limit);
        }
        else if ($request->ge == "r" && $request->insc == "r") {
            $books = Book::orderBy('books.genre','asc')->paginate($limit);
        }
        else if ($request->ge == "r" && $request->desc == "r") {
            $books = Book::orderBy('books.genre','desc')->paginate($limit);
        }
        else if ($request->pub == "r" && $request->insc == "r") {
            $books = Book::orderBy('books.publisher','asc')->paginate($limit);
        }
        else if ($request->pub == "r" && $request->desc == "r") {
            $books = Book::orderBy('books.publisher','desc')->paginate($limit);
        }
        else if ($request->trans == "r" && $request->insc == "r") {
            $books = Book::orderBy('books.translator','asc')->paginate($limit);
        }
        else if ($request->trans == "r" && $request->desc == "r") {
            $books = Book::orderBy('books.translator','desc')->paginate($limit);
        }
        else if ($request->coun == "r" && $request->insc == "r") {
            $books = Book::orderBy('books.country','asc')->paginate($limit);
        }
        else if ($request->coun == "r" && $request->desc == "r") {
            $books = Book::orderBy('books.country','desc')->paginate($limit);
        }
        else if ($request->qua == "r" && $request->insc == "r") {
            $books = Book::orderBy('books.copies','asc')->paginate($limit);
        }
        else if ($request->qua == "r" && $request->desc == "r") {
            $books = Book::orderBy('books.copies','desc')->paginate($limit);
        }
        else if ($request->pri == "r" && $request->insc == "r") {
            $books = Book::orderBy('books.price','asc')->paginate($limit);
        }
        else if ($request->pri == "r" && $request->desc == "r") {
            $books = Book::orderBy('books.price','desc')->paginate($limit);
        }
        else {
        $books = Book::paginate($limit);
        }
        return view('manageBook', compact('books', 'active'));
    }

    public function showBorrowedBookList(Request $request)
    {
        $active = "check";
        $limit = 5;
        $id = session('userId');
        if ($request->dc == "r" && $request->insc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.ddcCode','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->dc == "r" && $request->desc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.ddcCode','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->bn == "r" && $request->insc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.name','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->bn == "r" && $request->desc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.name','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->au == "r" && $request->insc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.author','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->au == "r" && $request->desc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.author','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->ge == "r" && $request->insc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.genre','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->ge == "r" && $request->desc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('books.genre','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->qua == "r" && $request->insc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('borrowings.quantity','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->qua == "r" && $request->desc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('borrowings.quantity','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->bd == "r" && $request->insc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('borrowings.borrowDate','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->bd == "r" && $request->desc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('borrowings.borrowDate','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->rd == "r" && $request->insc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('borrowings.returnDate','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        else if ($request->rd == "r" && $request->desc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', '!=',"returned")
                            ->orderBy('borrowings.returnDate','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }  
        else {
        $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)

                ->where('borrowings.returned', '!=', "returned")
                ->orderBy('borrowings.id', 'desc')
                ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.id as borrowId']);
        }
        return view('borrowedBookList', compact('books', 'active'));
    }

    public function showReturnedBookList(Request $request)
    {
        $active = "check";
        $limit = 5;
        $id = session('userId');
        if ($request->dc == "r" && $request->insc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned',"returned")
                            ->orderBy('books.ddcCode','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->dc == "r" && $request->desc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned',"returned")
                            ->orderBy('books.ddcCode','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->bn == "r" && $request->insc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('books.name','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->bn == "r" && $request->desc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('books.name','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->au == "r" && $request->insc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('books.author','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->au == "r" && $request->desc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned',"returned")
                            ->orderBy('books.author','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->ge == "r" && $request->insc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('books.genre','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->ge == "r" && $request->desc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('books.genre','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->qua == "r" && $request->insc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('borrowings.quantity','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->qua == "r" && $request->desc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('borrowings.quantity','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->bd == "r" && $request->insc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('borrowings.borrowDate','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->bd == "r" && $request->desc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('borrowings.borrowDate','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->rd == "r" && $request->insc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('borrowings.returnDate','asc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        else if ($request->rd == "r" && $request->desc == "r") {
            $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->orderBy('borrowings.returnDate','desc')
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }  
        else {
        $books = Borrowing::join('books', 'borrowings.bookId', '=', 'books.id')
                            ->where('borrowings.userId', $id)
                            ->where('borrowings.returned', "returned")
                            ->paginate($limit, ['books.ddcCode', 'books.name', 'books.author', 'books.genre', 'borrowings.quantity', 'borrowings.borrowDate', 'borrowings.returnDate', 'borrowings.returned', 'books.image', 'books.id', 'borrowings.updated_at as bookReturnedAt']);
        }
        $returned = true;
        return view('borrowedBookList', compact('books', 'returned', 'active'));
    }

    public function checkBorrower(Request $request)
    {
        $active = "manage";
        $message = "";
        if ($request->notExist) {
            $message = "Tài khoản không tồn tại";
        }
        return view('checkBorrower', compact('active', 'message'));
    }
    public function checkBorrowerPost(Request $request)
    {
        if (isset($_POST['check'])) {
            // code...

            $username = $request->username;
            $exist = User::where('username', $username)->first();
            if (!empty($exist)) {
                return redirect()->route('addBorrowing', compact('username'));
            } else {
                $notExist = true;
                return redirect()->route('checkBorrower', compact('notExist'));
            }
        }
        if (isset($_POST['signup'])) {
            //check username already exists
            $acc = User::where('username', $request->username)->first();
            if (isset($acc)) {
                toast('Tài khoản đã tồn tại', 'info');
                return redirect(route('checkBorrower'));
            }
            if ($request->name == "") {
                toast('Dữ liệu bạn nhập không đúng', 'info');
                return redirect(route('checkBorrower'));
            }

            $user = User::create([
                'username' => $request->username,
                'password' => Hash::make($request->username),
                'role' => 'guest'
            ]);

            $userId = $user->id;


            // dd($userId);
            UserInfo::create([
                'userId' => $userId,
                'name' => $request->name
            ]);

            toast('Đăng ký thành công', 'success');
            return redirect(route('checkBorrower'));
        }
    }
    public function addBorrowing(Request $request)
    {
        $active = 'manage';
        $username = $request->username;
        $borrower = User::where('username', $username)->first();
        $borrowerInfo = UserInfo::where('userId', $borrower->id)->first();
        // dd($borrower);
        return view('addBorrowing', compact('active', 'borrower', 'borrowerInfo'));
    }

    public function addBorrowingPost(Request $request)
    {
        $book = Book::where('name', $request->bookName)->first();
        if (!isset($book)) {
            $active = 'manage';
            $borrower = User::where('userId', $request->userId)->leftJoin('userInfo', 'userInfo.userId', 'users.id')->first();
            toast('Không tìm thấy sách đã nhập', 'info');
            return view('addBorrowing', compact('active', 'borrower'));
        }
        if (date('Y-m-d') >= $request->returnDate) {
            $active = 'manage';
            $borrower = User::where('userId', $request->userId)->leftJoin('userInfo', 'userInfo.userId', 'users.id')->first();
            toast('Ngày dự định trả không hợp lệ', 'info');
            return view('addBorrowing', compact('active', 'borrower'));
        }
        Borrowing::create([
            'userId' => $request->userId,
            'bookId' => $book->id,
            'quantity' => 1,
            'borrowDate' => date('Y-m-d'),
            'returnDate' => $request->returnDate,
            'returned' => 'borrowing'
        ]);
        $book->copies = 0;
        $book->save();
        toast('Thêm đơn thành công', 'success');
        return redirect(route('checkBorrower'));
    }
}
