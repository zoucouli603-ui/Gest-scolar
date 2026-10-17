<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\ClassRoom;
use App\Models\Expense;
use App\Support\SearchFilter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();
        $classId = $request->integer('class_room_id') ?: null;
        $statsQuery = Payment::query()
            ->when($currentYear, fn ($payments) => $payments->where('school_year_id', $currentYear->id))
            ->when($classId, fn ($payments) => $payments->where('class_room_id', $classId));
        $query = Payment::with(['student.classRoom', 'classRoom'])
            ->when($currentYear, fn ($payments) => $payments->where('school_year_id', $currentYear->id))
            ->when($classId, fn ($payments) => $payments->where('class_room_id', $classId));
        $expenseQuery = Expense::query()
            ->when($currentYear, fn ($expenses) => $expenses->where('school_year_id', $currentYear->id));

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            SearchFilter::apply($query, $search, ['reference'], function ($group, $like) {
                $group->orWhereHas('student', function ($students) use ($like) {
                    $students->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('registration_number', 'like', $like);
                });
            });
        }

        $totalCollected = (clone $statsQuery)->sum('amount');
        $paymentsCount = (clone $statsQuery)->count();
        $monthlyCollected = (clone $statsQuery)
            ->whereBetween('payment_date', [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()])
            ->sum('amount');
        $payments = $query->orderByDesc('payment_date')->orderByDesc('id')->get();
        $totalExpenses = (clone $expenseQuery)->sum('amount');
        $todayCollected = (clone $statsQuery)->whereDate('payment_date', today())->sum('amount');
        $expenses = $expenseQuery->with('teacher')->orderByDesc('expense_date')->orderByDesc('id')->get();
        $classes = $currentYear
            ? ClassRoom::where('school_year_id', $currentYear->id)->orderBy('name')->get()
            : collect();
        $lyceeClasses = $classes->where('cycle', 'Lycée')->values();
        $lyceeStudents = $currentYear && $lyceeClasses->isNotEmpty()
            ? Student::whereIn('class_room_id', $lyceeClasses->pluck('id'))
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(['id', 'first_name', 'last_name', 'class_room_id'])
            : collect();
        $expenseCategories = ExpenseController::CATEGORIES;

        return view('finances.index', compact(
            'currentYear',
            'payments',
            'totalCollected',
            'paymentsCount',
            'monthlyCollected',
            'totalExpenses',
            'todayCollected',
            'expenses',
            'expenseCategories',
            'classes',
            'classId',
            'lyceeClasses',
            'lyceeStudents'
        ));
    }

    public function create()
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (! $currentYear) {
            return redirect()->route('finances.index')
                ->with('error', 'Veuillez définir une année scolaire active avant d’enregistrer un paiement.');
        }

        $classes = ClassRoom::where('school_year_id', $currentYear->id)
            ->orderBy('name')
            ->get();
        $students = Student::with('classRoom')
            ->where('school_year_id', $currentYear->id)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('finances.create', compact('currentYear', 'classes', 'students'));
    }

    public function store(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (! $currentYear) {
            return redirect()->route('finances.index')
                ->with('error', 'Aucune année scolaire active trouvée.');
        }

        $validated = $request->validate([
            'class_room_id' => [
                'required',
                Rule::exists('class_rooms', 'id')->where('school_year_id', $currentYear->id),
            ],
            'student_id' => [
                'required',
                Rule::exists('students', 'id')
                    ->where('school_year_id', $currentYear->id)
                    ->where('class_room_id', $request->input('class_room_id')),
            ],
            'amount' => ['required', 'integer', 'min:1', 'max:999999999'],
            'fee_type' => ['required', Rule::in(['inscription', 'scolarite'])],
            'fee_month' => [
                Rule::requiredIf($request->input('fee_type') === 'scolarite'),
                'nullable',
                'date_format:Y-m',
            ],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_method' => ['required', Rule::in(['espèces', 'mobile_money', 'virement', 'carte', 'autre'])],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
        ], [
            'student_id.exists' => 'Choisissez un élève de l’année scolaire active.',
            'class_room_id.exists' => 'Choisissez une classe de l’année scolaire active.',
            'amount.integer' => 'Le montant en FCFA doit être un nombre entier.',
            'amount.min' => 'Le montant doit être supérieur à zéro.',
            'payment_date.before_or_equal' => 'La date du paiement ne peut pas être dans le futur.',
        ]);

        $validated['fee_month'] = $validated['fee_type'] === 'scolarite'
            ? $validated['fee_month'].'-01'
            : null;
        $validated['school_year_id'] = $currentYear->id;
        $payment = DB::transaction(function () use ($validated) {
            $payment = Payment::create($validated);
            $payment->update([
                'receipt_number' => sprintf('REC-%s-%06d', $payment->payment_date->format('Y'), $payment->id),
            ]);

            return $payment;
        });

        return redirect()->route('finances.receipt', $payment)
            ->with('success', 'Paiement enregistré avec succès.');
    }

    public function storeSubsidy(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (! $currentYear) {
            return redirect()->route('finances.index')
                ->with('error', 'Aucune année scolaire active trouvée.');
        }

        $validated = $request->validate([
            'class_room_id' => [
                'required',
                Rule::exists('class_rooms', 'id')
                    ->where('school_year_id', $currentYear->id)
                    ->where('cycle', 'Lycée'),
            ],
            'student_id' => [
                'required',
                Rule::exists('students', 'id')
                    ->where('school_year_id', $currentYear->id)
                    ->where('class_room_id', $request->input('class_room_id')),
            ],
            'amount' => ['required', 'integer', 'min:1', 'max:999999999'],
            'fee_type' => ['required', Rule::in(['inscription', 'scolarite'])],
            'fee_month' => [
                Rule::requiredIf($request->input('fee_type') === 'scolarite'),
                'nullable',
                'date_format:Y-m',
            ],
            'payment_date' => ['required', 'date', 'before_or_equal:today'],
            'reference' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:900'],
        ], [
            'class_room_id.exists' => 'Les subventions concernent uniquement les classes du Lycée de l’année active.',
            'student_id.exists' => 'Choisissez un élève de la classe sélectionnée.',
            'amount.integer' => 'Le montant en FCFA doit être un nombre entier.',
            'payment_date.before_or_equal' => 'La date ne peut pas être dans le futur.',
        ]);

        $validated['fee_month'] = $validated['fee_type'] === 'scolarite'
            ? $validated['fee_month'].'-01'
            : null;
        $validated['school_year_id'] = $currentYear->id;
        $validated['payment_method'] = 'autre';
        $validated['is_subsidy'] = true;
        $validated['description'] = trim('Subvention de l’État. '.($validated['description'] ?? ''));

        $payment = DB::transaction(function () use ($validated) {
            $payment = Payment::create($validated);
            $payment->update([
                'receipt_number' => sprintf('SUB-%s-%06d', $payment->payment_date->format('Y'), $payment->id),
            ]);

            return $payment;
        });

        return redirect()->route('finances.receipt', $payment)
            ->with('success', 'Subvention enregistrée avec succès.');
    }

    public function receipt(Payment $payment)
    {
        $payment->load(['student.classRoom', 'classRoom', 'schoolYear']);

        return view('finances.receipt', compact('payment'));
    }

    public function destroy(Payment $payment)
    {
        $payment->delete();

        return redirect()->route('finances.index')->with('success', 'Paiement supprimé.');
    }
}
