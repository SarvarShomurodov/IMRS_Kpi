<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AssignmentController extends Controller
{
    public function __construct()
    {
        // Super Admin va Adminlarga barcha fayllarni ko'rish, tahrirlash, o'chirishga ruxsat berish
        $this->middleware('permission:view assignments|edit assignments|delete assignments', ['only' => ['index', 'edit', 'update', 'destroy']]);

        // Userga faqat o'z fayllarini ko'rish, tahrirlash va o'chirishga ruxsat berish
        $this->middleware('permission:view own assignment|edit own assignment|delete own assignment', ['only' => ['show', 'edit', 'update', 'destroy']]);
    }

    public function index(Request $request)
    {
        $query = Assignment::query();

        // Year va month'ni olish
        $selectedYear = $request->get('year');
        $selectedMonth = $request->get('month');

        // ✅ Qidiruv - GLOBAL (filtrlardan mustaqil)
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where('name', 'LIKE', "%{$search}%");

            // ✅ Qidiruv paytida yil/oy filtrlarini e'tiborsiz qoldirish
            // Faqat show_all va search bo'lsa - filtr qo'llamaymiz
        } else {
            // ✅ Agar qidiruv YO'Q bo'lsa - oddiy filtrlar ishlaydi
            if (!$request->has('show_all') && $selectedYear && $selectedMonth) {
                $year = (int) $selectedYear;
                $month = (int) $selectedMonth;

                $startDate = Carbon::create($year, $month, 26)->subMonth()->startOfDay();
                $endDate = Carbon::create($year, $month, 25)->endOfDay();

                $query->whereBetween('date', [$startDate, $endDate]);
            }
        }

        // Foydalanuvchi agar "User" bo'lsa, faqat o'z fayllari
        if (Auth::user()->hasRole('User')) {
            $query->where('user_id', Auth::id());
        }

        $assignments = $query->orderBy('date', 'desc')->paginate(10);

        $months = [
            1 => 'Yanvar',
            2 => 'Fevral',
            3 => 'Mart',
            4 => 'Aprel',
            5 => 'May',
            6 => 'Iyun',
            7 => 'Iyul',
            8 => 'Avgust',
            9 => 'Sentyabr',
            10 => 'Oktyabr',
            11 => 'Noyabr',
            12 => 'Dekabr',
        ];

        $currentYear = date('Y');
        $years = range($currentYear - 2, $currentYear + 2);

        return view('client.assignments.index', compact('assignments', 'months', 'years', 'selectedYear', 'selectedMonth'));
    }
    public function show($id)
    {
        $assignment = Assignment::findOrFail($id);

        // Faqat o'z fayllarini ko'rish
        if (Auth::user()->cannot('view', $assignment)) {
            abort(403);
        }

        return view('client.assignments.show', compact('assignment'));
    }

    public function create()
    {
        return view('client.assignments.create');
    }

    public function store(Request $request)
    {
        // Validatsiya
        $request->validate([
            'name' => 'required',
            'who_from' => 'required',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xlsx,zip|max:30720',
            'date' => 'nullable|date',
            'who_hand' => 'nullable|string',
            'people' => 'nullable|string',
        ]);

        // Faylni saqlash
        $filePath = null;
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = Auth::user()->lastName . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('public/assignments', $fileName);
        }

        // Assignment yaratish
        $assignment = Assignment::create([
            'name' => $request->input('name'),
            'who_from' => $request->input('who_from'),
            'file' => $filePath,
            'date' => $request->input('date'),
            'who_hand' => $request->input('who_hand'),
            'people' => $request->input('people'),
            'user_id' => Auth::id(), // Faylni kim yuklaganini saqlash
        ]);

        return redirect()
            ->route('assignments.index', [
                'year' => request('year'),
                'month' => request('month'),
            ])
            ->with('success', 'Assignment created successfully.');
    }

    public function edit($id)
    {
        $assignment = Assignment::findOrFail($id);

        // Faqat o'z faylini tahrirlash
        if (Auth::user()->cannot('update', $assignment)) {
            abort(403);
        }

        return view('client.assignments.edit', compact('assignment'));
    }

    public function update(Request $request, $id)
    {
        // Validatsiya
        $request->validate([
            'name' => 'required',
            'who_from' => 'required',
            'file' => 'nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx,xlsx,zip|max:30720',
            'date' => 'nullable|date',
            'who_hand' => 'nullable|string',
            'people' => 'nullable|string',
        ]);

        $assignment = Assignment::findOrFail($id);
        $filePath = $assignment->file; // Eskirgan fayl yo'li

        // Faylni saqlash
        if ($request->hasFile('file')) {
            // Agar yangi fayl yuklansa, eski faylni o'chirish
            if ($filePath) {
                Storage::delete($filePath);
            }

            $file = $request->file('file');
            $fileName = Auth::user()->name . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = $file->storeAs('public/assignments', $fileName);
        }

        // Assignmentni yangilash
        $assignment->update([
            'name' => $request->input('name'),
            'who_from' => $request->input('who_from'),
            'file' => $filePath,
            'date' => $request->input('date'),
            'who_hand' => $request->input('who_hand'),
            'people' => $request->input('people'),
        ]);

        // return redirect()->route('assignments.index')->with('success', 'Assignment updated successfully.');
        return redirect()
            ->route('assignments.index', [
                'year' => request('year'),
                'month' => request('month'),
            ])
            ->with('success', 'Assignment updated successfully.');
    }
    public function viewFile(Assignment $assignment)
    {
        // Fayl mavjudligini tekshirish
        if (!$assignment->file || !Storage::exists($assignment->file)) {
            abort(404, 'Fayl topilmadi');
        }

        // Faylni browserda ochish
        return Storage::response($assignment->file);

        // Yoki yuklab olish uchun:
        // return Storage::download($assignment->file);
    }
    public function destroy($id)
    {
        $assignment = Assignment::findOrFail($id);

        // Faqat o'z faylini o'chirish
        if (Auth::user()->cannot('delete', $assignment)) {
            abort(403);
        }

        // Faylni o'chirish
        if ($assignment->file) {
            Storage::delete($assignment->file);
        }

        $assignment->delete();

        return redirect()
            ->route('assignments.index', [
                'year' => request('year'),
                'month' => request('month'),
            ])
            ->with('success', 'Assignment deleted successfully.');
    }
    
}
