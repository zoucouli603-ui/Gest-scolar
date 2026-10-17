<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\SchoolYear;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ExpenseController extends Controller
{
    public const CATEGORIES = [
        'salaires' => 'Salaires',
        'fournitures' => 'Fournitures',
        'entretien' => 'Entretien',
        'eau_electricite' => 'Eau et électricité',
        'transport' => 'Transport',
        'autre' => 'Autre',
    ];

    public function create()
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (! $currentYear) {
            return redirect()->route('finances.index')
                ->with('error', 'Veuillez définir une année scolaire active avant d’enregistrer une dépense.');
        }

        return view('finances.expenses.create', [
            'currentYear' => $currentYear,
            'categories' => self::CATEGORIES,
            'teachers' => Teacher::where('school_year_id', $currentYear->id)
                ->where('status', 'actif')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get(),
        ]);
    }

    public function store(Request $request)
    {
        $currentYear = SchoolYear::where('is_current', true)->first();

        if (! $currentYear) {
            return redirect()->route('finances.index')
                ->with('error', 'Aucune année scolaire active trouvée.');
        }

        $validated = $request->validate([
            'expense_date' => ['required', 'date', 'before_or_equal:today'],
            'category' => ['required', Rule::in(array_keys(self::CATEGORIES))],
            'teacher_id' => [
                Rule::requiredIf($request->input('category') === 'salaires'),
                'nullable',
                'integer',
                Rule::exists('teachers', 'id')->where('school_year_id', $currentYear->id),
            ],
            'amount' => ['required', 'integer', 'min:1', 'max:999999999'],
            'description' => ['required', 'string', 'max:1000'],
        ]);

        if ($validated['category'] !== 'salaires') {
            $validated['teacher_id'] = null;
        }

        $validated['school_year_id'] = $currentYear->id;
        Expense::create($validated);

        return redirect()->route('finances.index')->with('success', 'Dépense enregistrée avec succès.');
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();

        return redirect()->route('finances.index')->with('success', 'Dépense supprimée.');
    }
}
