<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Expense;
use App\Models\Note;
use App\Models\Payment;
use App\Models\School;
use App\Models\SchoolYear;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeachingAssignment;
use App\Models\TimetableEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchoolCycleAndReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_class_can_be_created_with_its_cycle_and_monthly_fee(): void
    {
        $user = $this->createSchoolOwner();
        $this->actingAs($user);
        SchoolYear::create([
            'school_id' => $user->school_id,
            'name' => '2026-2027',
            'is_current' => true,
        ]);

        $this->post(route('classes.store'), [
            'cycle' => 'Maternelle',
            'name' => 'Petite section',
            'monthly_fee' => 125000,
        ])->assertRedirect(route('classes.index'));

        $this->assertDatabaseHas('class_rooms', [
            'name' => 'Petite section',
            'cycle' => 'Maternelle',
            'monthly_fee' => 125000,
        ]);
    }

    public function test_report_totals_and_cycle_breakdown_use_the_selected_school_year(): void
    {
        $user = $this->createSchoolOwner();
        $schoolYear = SchoolYear::create([
            'school_id' => $user->school_id,
            'name' => '2026-2027',
            'is_current' => true,
        ]);
        $classRoom = ClassRoom::create([
            'name' => 'Petite section',
            'cycle' => 'Maternelle',
            'monthly_fee' => 100000,
            'school_year_id' => $schoolYear->id,
        ]);
        $student = Student::create([
            'registration_number' => 'DEM-TEST-0001',
            'first_name' => 'Awa',
            'last_name' => 'Diallo',
            'gender' => 'F',
            'birth_date' => '2018-04-01',
            'birth_place' => 'Bamako',
            'nationality' => 'Malienne',
            'inscription_date' => '2026-09-01',
            'class_name' => $classRoom->name,
            'class_room_id' => $classRoom->id,
            'parent_name' => 'Parent Diallo',
            'parent_phone' => '70000000',
            'school_year_id' => $schoolYear->id,
            'status' => 'actif',
        ]);
        $subject = Subject::create([
            'name' => 'Mathématiques',
            'code' => 'MAT-REPORT',
            'coefficient' => 2,
            'school_year_id' => $schoolYear->id,
        ]);
        $teacher = Teacher::create([
            'first_name' => 'Moussa',
            'last_name' => 'Traoré',
            'monthly_salary' => 75000,
            'status' => 'actif',
            'school_year_id' => $schoolYear->id,
        ]);
        $teacher->declaredSubjects()->attach($subject);
        Payment::create([
            'student_id' => $student->id,
            'class_room_id' => $classRoom->id,
            'school_year_id' => $schoolYear->id,
            'amount' => 50000,
            'fee_type' => 'scolarite',
            'payment_date' => today(),
            'fee_month' => today()->startOfMonth(),
            'payment_method' => 'espèces',
        ]);
        Expense::create([
            'school_year_id' => $schoolYear->id,
            'expense_date' => today(),
            'category' => 'salaires',
            'amount' => 10000,
            'description' => 'Salaire du mois',
            'teacher_id' => $teacher->id,
        ]);
        Expense::create([
            'school_year_id' => $schoolYear->id,
            'expense_date' => today(),
            'category' => 'Fournitures',
            'amount' => 3000,
            'description' => 'Fournitures scolaires',
        ]);

        $this->actingAs($user)
            ->get(route('reports.index', [
                'school_year_id' => $schoolYear->id,
                'cycle' => 'Maternelle',
                'period_type' => 'monthly',
                'period' => today()->format('Y-m'),
            ]))
            ->assertOk()
            ->assertSee('Effectifs et frais mensuels par cycle et par classe')
            ->assertSee('Petite section')
            ->assertSee('Élèves débiteurs du mois')
            ->assertSee('Diallo Awa')
            ->assertSee('Traoré Moussa')
            ->assertSee('Mathématiques')
            ->assertSee('100 000 XOF')
            ->assertSee('50 000 XOF')
            ->assertSee('13 000 XOF')
            ->assertSee('10 000 XOF')
            ->assertSee('Imprimer la fiche');
        $this->assertSame(['Maternelle', '1er Cycle', '2è Cycle', 'Lycée'], ClassRoom::CYCLES);
    }

    public function test_teacher_can_be_created_with_monthly_salary_and_existing_subjects(): void
    {
        $user = $this->createSchoolOwner();
        $schoolYear = SchoolYear::create([
            'school_id' => $user->school_id,
            'name' => '2026-2027',
            'is_current' => true,
        ]);
        $subject = Subject::create([
            'name' => 'Français',
            'code' => 'FRA-TEACHER',
            'coefficient' => 1,
            'school_year_id' => $schoolYear->id,
        ]);

        $this->actingAs($user)
            ->post(route('teachers.store'), [
                'first_name' => 'Aminata',
                'last_name' => 'Coulibaly',
                'status' => 'actif',
                'subject_ids' => [$subject->id],
                'class_ids_present' => '1',
            ])
            ->assertRedirect(route('teachers.index'));

        $teacher = Teacher::where('email', null)->where('first_name', 'Aminata')->firstOrFail();
        $this->assertTrue($teacher->declaredSubjects->contains($subject));
        $this->assertDatabaseHas('teacher_subject', [
            'teacher_id' => $teacher->id,
            'subject_id' => $subject->id,
        ]);
    }

    public function test_primary_composition_is_monthly_and_scored_out_of_ten(): void
    {
        [$user, $year, $classRoom, $student, $subjects, $teachers] = $this->createGradeBook('Primaire');
        TeachingAssignment::create([
            'school_year_id' => $year->id,
            'teacher_id' => $teachers[0]->id,
            'subject_id' => $subjects[0]->id,
            'class_room_id' => $classRoom->id,
        ]);

        $payload = [
            'class_room_id' => $classRoom->id,
            'student_id' => $student->id,
            'subject_id' => $subjects[0]->id,
            'evaluation_type' => 'composition',
            'evaluation_month' => '2026-11',
            'score' => 8.5,
        ];

        $this->actingAs($user)->post(route('notes.store'), $payload)
            ->assertRedirect(route('notes.index', [
                'class_room_id' => $classRoom->id,
                'student_id' => $student->id,
                'evaluation_month' => '2026-11',
            ]));
        $this->actingAs($user)->post(route('notes.store'), array_merge($payload, ['score' => 9]))
            ->assertSessionHasNoErrors();

        $this->assertSame(1, Note::count());
        $this->assertDatabaseHas('notes', [
            'class_room_id' => $classRoom->id,
            'evaluation_type' => 'composition',
            'score' => 9,
            'max_score' => 10,
            'note_date' => '2026-11-01 00:00:00',
        ]);

        $this->get(route('notes.bulletin', [
            'class_room_id' => $classRoom->id,
            'student_id' => $student->id,
            'evaluation_month' => '2026-11',
        ]))
            ->assertOk()
            ->assertSee('Bulletin de notes')
            ->assertSee('9,00 / 10')
            ->assertSee('MATIÈRES')
            ->assertSee('Moyenne du 1er')
            ->assertSee('Visa des parents')
            ->assertSee('Excellent')
            ->assertSee('writing-mode: vertical-rl')
            ->assertDontSee('Composition de novembre 2026');
    }

    public function test_primary_class_coefficient_is_saved_and_used_by_the_annual_bulletin(): void
    {
        [$user, $year, $classRoom, $student, $subjects, $teachers] = $this->createGradeBook('1er Cycle');
        $assignment = TeachingAssignment::create([
            'school_year_id' => $year->id,
            'teacher_id' => $teachers[0]->id,
            'subject_id' => $subjects[0]->id,
            'class_room_id' => $classRoom->id,
        ]);

        $this->actingAs($user)
            ->get(route('notes.index', [
                'class_room_id' => $classRoom->id,
                'subject_id' => $subjects[0]->id,
                'evaluation_month' => '2026-11',
            ]))
            ->assertOk()
            ->assertSee('Coefficient')
            ->assertSee('NOTE /10')
            ->assertDontSee('Note du trimestre /10');

        $this->post(route('notes.store'), [
            'class_room_id' => $classRoom->id,
            'subject_id' => $subjects[0]->id,
            'evaluation_type' => 'composition',
            'evaluation_month' => '2026-11',
            'class_scores' => [$student->id => 8],
            'coefficient' => 3,
        ])->assertRedirect(route('notes.index', [
            'class_room_id' => $classRoom->id,
            'subject_id' => $subjects[0]->id,
            'evaluation_month' => '2026-11',
        ]));

        $this->assertDatabaseHas('teaching_assignments', [
            'id' => $assignment->id,
            'coefficient' => 3,
        ]);

        $this->get(route('notes.bulletin', [
            'class_room_id' => $classRoom->id,
            'student_id' => $student->id,
            'evaluation_month' => '2026-11',
        ]))
            ->assertOk()
            ->assertSee('Moyenne annuelle')
            ->assertSee('Mathématiques')
            ->assertSee('8,00')
            ->assertSee('24,00');

        $this->get(route('notes.class-bulletin', [
            'class_room_id' => $classRoom->id,
            'evaluation_month' => '2026-11',
        ]))
            ->assertOk()
            ->assertSee('Moyenne annuelle')
            ->assertSee('Mathématiques')
            ->assertSee('24,00')
            ->assertSee('MATIÈRES')
            ->assertSee('Signature du maître')
            ->assertDontSee('Composition de novembre 2026');
    }

    public function test_secondary_accepts_multiple_assessments_and_averages_them_with_the_exam(): void
    {
        [$user, $year, $classRoom, $student, $subjects] = $this->createGradeBook('2è Cycle');
        TeachingAssignment::create([
            'school_year_id' => $year->id,
            'teacher_id' => Teacher::where('school_year_id', $year->id)->firstOrFail()->id,
            'subject_id' => $subjects[0]->id,
            'class_room_id' => $classRoom->id,
        ]);
        $this->actingAs($user);

        foreach ([
            ['devoir', 10],
            ['controle', 12],
            ['devoir', 14],
            ['examen_trimestriel', 18],
        ] as [$type, $score]) {
            $this->post(route('notes.store'), [
                'class_room_id' => $classRoom->id,
                'student_id' => $student->id,
                'subject_id' => $subjects[0]->id,
                'evaluation_type' => $type,
                'trimester' => 1,
                'note_date' => '2026-11-15',
                'score' => $score,
            ])->assertSessionHasNoErrors();
        }

        $this->post(route('teaching-assignments.store'), [
            'teacher_id' => Teacher::where('school_year_id', $year->id)->latest('id')->firstOrFail()->id,
            'subject_id' => $subjects[1]->id,
            'class_room_id' => $classRoom->id,
        ])->assertRedirect(route('teaching-assignments.index'));

        $this->assertSame(4, Note::count());
        $this->assertSame(2, TeachingAssignment::count());
        $topStudent = Student::create([
            'registration_number' => 'GRADE-SECONDARY-TOP',
            'first_name' => 'Mariam',
            'last_name' => 'Traoré',
            'gender' => 'F',
            'birth_date' => '2013-05-12',
            'birth_place' => 'Sikasso',
            'nationality' => 'Malienne',
            'inscription_date' => '2026-09-01',
            'class_name' => $classRoom->name,
            'class_room_id' => $classRoom->id,
            'parent_name' => 'Parent Traoré',
            'parent_phone' => '71000000',
            'school_year_id' => $year->id,
            'status' => 'actif',
        ]);
        foreach ([
            ['devoir', 18],
            ['examen_trimestriel', 20],
        ] as [$type, $score]) {
            Note::create([
                'school_year_id' => $year->id,
                'student_id' => $topStudent->id,
                'subject_id' => $subjects[0]->id,
                'class_room_id' => $classRoom->id,
                'teacher_id' => TeachingAssignment::firstOrFail()->teacher_id,
                'evaluation_type' => $type,
                'trimester' => 1,
                'score' => $score,
                'max_score' => 20,
                'note_date' => '2026-11-15',
            ]);
        }
        $this->get(route('notes.bulletin', [
            'class_room_id' => $classRoom->id,
            'student_id' => $student->id,
            'trimester' => 1,
        ]))
            ->assertOk()
            ->assertSee('Moyenne /20')
            ->assertSee('13,50')
            ->assertSee('Date et lieu de naissance')
            ->assertSee('Parent / tuteur')
            ->assertSee('Moyenne du premier')
            ->assertSee('Assez-bien')
            ->assertSee('Rang')
            ->assertSee('2e')
            ->assertSee('19,00')
            ->assertSee('size: A4 landscape')
            ->assertSee('Signature du maître')
            ->assertSee('Signature du Directeur')
            ->assertSee('Visa des parents');

        $this->get(route('notes.class-bulletin', [
            'class_room_id' => $classRoom->id,
            'trimester' => 1,
        ]))
            ->assertOk()
            ->assertSee('Matières')
            ->assertSee('Composition /20')
            ->assertSee('Moyenne du premier')
            ->assertSee('Signature du maître')
            ->assertSee('Signature du Directeur')
            ->assertSee('Visa des parents')
            ->assertSee('Diallo Awa')
            ->assertSee('Bamako')
            ->assertSee('Traoré Mariam')
            ->assertSee('19,00')
            ->assertSee('Coefficient')
            ->assertSee('Moyenne générale')
            ->assertSee('Points coef.')
            ->assertSee('size: A4 landscape');
    }

    public function test_high_school_bulletins_include_student_details_subject_ranks_and_teacher_visa(): void
    {
        [$user, $year, $classRoom, $student, $subjects, $teachers] = $this->createGradeBook('Lycée');
        TeachingAssignment::create([
            'school_year_id' => $year->id,
            'teacher_id' => $teachers[0]->id,
            'subject_id' => $subjects[0]->id,
            'class_room_id' => $classRoom->id,
            'coefficient' => 3,
        ]);

        foreach ([
            ['devoir', 14],
            ['examen_trimestriel', 16],
        ] as [$type, $score]) {
            Note::create([
                'school_year_id' => $year->id,
                'student_id' => $student->id,
                'subject_id' => $subjects[0]->id,
                'class_room_id' => $classRoom->id,
                'teacher_id' => $teachers[0]->id,
                'evaluation_type' => $type,
                'trimester' => 1,
                'score' => $score,
                'max_score' => 20,
                'note_date' => '2026-11-15',
            ]);
        }

        $this->actingAs($user);
        $this->get(route('notes.bulletin', [
            'class_room_id' => $classRoom->id,
            'student_id' => $student->id,
            'trimester' => 1,
        ]))
            ->assertOk()
            ->assertSee('Date et lieu de naissance')
            ->assertSee('Parent / tuteur')
            ->assertSee('Effectif de la classe')
            ->assertSee("Date d'édition", false)
            ->assertSee('Rang')
            ->assertSee('Note de Classe')
            ->assertSee('Note de Comp /20')
            ->assertSee('Moy Général')
            ->assertSee('Appréciations')
            ->assertSee('Moyenne de l’élève')
            ->assertDontSee('visa des professeurs')
            ->assertDontSee('Moyenne de la classe :')
            ->assertDontSee('Moyenne du 1er :')
            ->assertSee('Signature du Directeur')
            ->assertSee('Visa des parents')
            ->assertSee('Bien')
            ->assertSee('size: A4 landscape');

        $this->get(route('notes.class-bulletin', [
            'class_room_id' => $classRoom->id,
            'trimester' => 1,
        ]))
            ->assertOk()
            ->assertSee('Note de Classe')
            ->assertSee('Note de Comp /20')
            ->assertSee('Moy Général')
            ->assertSee('Appréciations')
            ->assertSee('Moyenne de l’élève')
            ->assertSee('Date et lieu de naissance')
            ->assertSee('Rang')
            ->assertDontSee('visa des professeurs')
            ->assertSee('size: A4 landscape');
    }

    public function test_timetable_print_grid_includes_break_rows_and_hides_navigation_search(): void
    {
        [$user, $year, $classRoom, , $subjects, $teachers] = $this->createGradeBook('Lycée');
        $assignment = TeachingAssignment::create([
            'school_year_id' => $year->id,
            'teacher_id' => $teachers[0]->id,
            'subject_id' => $subjects[0]->id,
            'class_room_id' => $classRoom->id,
        ]);
        foreach ([[1, '08:00', '09:45'], [1, '14:00', '15:00']] as [$day, $start, $end]) {
            TimetableEntry::create([
                'school_year_id' => $year->id,
                'teaching_assignment_id' => $assignment->id,
                'day_of_week' => $day,
                'starts_at' => $start,
                'ends_at' => $end,
            ]);
        }

        $this->actingAs($user)->get(route('timetable.index', ['class_room_id' => $classRoom->id]))
            ->assertOk()
            ->assertSee('09:45')
            ->assertSee('RÉCRÉATION')
            ->assertSee('APRÈS-MIDI')
            ->assertSee('#global-search');
    }

    public function test_certificates_name_the_director_and_school_report_shows_term_averages_only(): void
    {
        [$user, $year, $classRoom, $student, $subjects, $teachers] = $this->createGradeBook('Lycée');
        $user->school->update(['director_name' => 'Mamadou Traoré']);
        foreach ([
            ['devoir', 10],
            ['controle', 14],
            ['examen_trimestriel', 18],
        ] as [$type, $score]) {
            Note::create([
                'school_year_id' => $year->id,
                'student_id' => $student->id,
                'subject_id' => $subjects[0]->id,
                'class_room_id' => $classRoom->id,
                'teacher_id' => $teachers[0]->id,
                'evaluation_type' => $type,
                'trimester' => 1,
                'score' => $score,
                'max_score' => 20,
                'note_date' => '2026-11-15',
            ]);
        }

        $this->actingAs($user);
        foreach (['scolarite', 'transfert'] as $type) {
            $query = $type === 'transfert' ? ['destination' => 'École d’accueil'] : [];
            $this->get(route('documents.print', [$student, $type]).'?'.http_build_query($query))
                ->assertOk()
                ->assertSee('Je soussigné(e), <strong>Mamadou Traoré, Directeur</strong> de l’établissement', false);
        }

        $this->get(route('documents.print', [$student, 'scolaire']))
            ->assertOk()
            ->assertSee('Année scolaire')
            ->assertSee('Moyenne annuelle')
            ->assertSee('Classe test')
            ->assertSee('15,00 / 20')
            ->assertDontSee('Examen trimestriel')
            ->assertDontSee('Devoir')
            ->assertDontSee('<th>Date</th>', false)
            ->assertDontSee('<th>Évaluation</th>', false)
            ->assertSee('<th>Parcours</th>', false);
    }

    public function test_school_record_shows_yearly_classes_annual_average_and_repeated_class(): void
    {
        [$user, $currentYear, $currentClass, $currentStudent, $subjects] = $this->createGradeBook('Lycée');
        $currentClass->update(['name' => 'Terminale']);
        $records = [];

        foreach (['2024-2025', '2025-2026'] as $index => $yearName) {
            $year = SchoolYear::create([
                'school_id' => $user->school_id,
                'name' => $yearName,
                'is_current' => false,
            ]);
            $classRoom = ClassRoom::create([
                'name' => 'Première A',
                'cycle' => 'Lycée',
                'school_year_id' => $year->id,
            ]);
            $student = Student::create([
                'registration_number' => $currentStudent->registration_number,
                'first_name' => $currentStudent->first_name,
                'last_name' => $currentStudent->last_name,
                'gender' => 'F',
                'birth_date' => $currentStudent->birth_date,
                'birth_place' => $currentStudent->birth_place,
                'nationality' => $currentStudent->nationality,
                'inscription_date' => '2024-09-01',
                'class_name' => $classRoom->name,
                'class_room_id' => $classRoom->id,
                'parent_name' => $currentStudent->parent_name,
                'parent_phone' => $currentStudent->parent_phone,
                'school_year_id' => $year->id,
                'status' => 'actif',
            ]);
            $records[] = [$year, $classRoom, $student];
        }

        foreach ([...$records, [$currentYear, $currentClass, $currentStudent]] as [$year, $classRoom, $student]) {
            foreach ([[1, 'devoir', 12], [1, 'examen_trimestriel', 16], [2, 'devoir', 14], [2, 'examen_trimestriel', 18], [3, 'devoir', 10], [3, 'examen_trimestriel', 14]] as [$trimester, $type, $score]) {
                Note::create([
                    'school_year_id' => $year->id,
                    'student_id' => $student->id,
                    'subject_id' => $subjects[0]->id,
                    'class_room_id' => $classRoom->id,
                    'evaluation_type' => $type,
                    'trimester' => $trimester,
                    'score' => $score,
                    'max_score' => 20,
                    'note_date' => $year->name === '2024-2025' ? '2025-01-15' : ($year->name === '2025-2026' ? '2026-01-15' : '2027-01-15'),
                ]);
            }
        }

        $this->actingAs($user)->get(route('documents.print', [$currentStudent, 'scolaire']))
            ->assertOk()
            ->assertSee('2024-2025')
            ->assertSee('2025-2026')
            ->assertSee('2026-2027')
            ->assertSee('Première A')
            ->assertSee('Terminale')
            ->assertSee('14,00 / 20')
            ->assertSee('Redoublée')
            ->assertSee('Passage');
    }

    public function test_bulletins_render_student_gender_as_text_not_an_html_entity(): void
    {
        [$user, $year, $classRoom, $student] = $this->createGradeBook('Lycée');

        $this->actingAs($user)
            ->get(route('notes.bulletin', [
                'class_room_id' => $classRoom->id,
                'student_id' => $student->id,
                'trimester' => 1,
            ]))
            ->assertOk()
            ->assertSee('Sexe :</strong> Féminin', false)
            ->assertDontSee('F&eacute;minin', false);
    }

    public function test_primary_school_report_groups_monthly_compositions_as_averages(): void
    {
        [$user, $year, $classRoom, $student, $subjects, $teachers] = $this->createGradeBook('1er Cycle');
        foreach ([8, 10] as $score) {
            Note::create([
                'school_year_id' => $year->id,
                'student_id' => $student->id,
                'subject_id' => $subjects[0]->id,
                'class_room_id' => $classRoom->id,
                'teacher_id' => $teachers[0]->id,
                'evaluation_type' => 'composition',
                'score' => $score,
                'max_score' => 10,
                'note_date' => '2026-11-15',
            ]);
        }

        $this->actingAs($user)->get(route('documents.print', [$student, 'scolaire']))
            ->assertOk()
            ->assertSee('2026-2027')
            ->assertSee('Classe test')
            ->assertSee('9,00 / 10')
            ->assertDontSee('Composition mensuelle')
            ->assertDontSee('Mathématiques');
    }

    public function test_maternelle_does_not_accept_numeric_grades(): void
    {
        [$user, $year, $classRoom, $student, $subjects] = $this->createGradeBook('Maternelle');

        $this->actingAs($user)->post(route('notes.store'), [
            'class_room_id' => $classRoom->id,
            'student_id' => $student->id,
            'subject_id' => $subjects[0]->id,
            'evaluation_type' => 'devoir',
            'trimester' => 1,
            'note_date' => '2026-11-15',
            'score' => 15,
        ])->assertStatus(422);

        $this->assertSame(0, Note::count());
    }

    public function test_primary_class_cannot_be_assigned_a_second_teacher(): void
    {
        [$user, $year, $classRoom, $student, $subjects, $teachers] = $this->createGradeBook('Primaire');
        TeachingAssignment::create([
            'school_year_id' => $year->id,
            'teacher_id' => $teachers[0]->id,
            'subject_id' => $subjects[0]->id,
            'class_room_id' => $classRoom->id,
        ]);

        $this->actingAs($user)->post(route('teaching-assignments.store'), [
            'teacher_id' => $teachers[1]->id,
            'subject_id' => $subjects[1]->id,
            'class_room_id' => $classRoom->id,
        ])->assertSessionHasErrors('teacher_id');

        $this->assertSame(1, TeachingAssignment::count());
    }

    public function test_salary_expense_form_exposes_selected_teachers_monthly_salary(): void
    {
        $user = $this->createSchoolOwner();
        $schoolYear = SchoolYear::create([
            'school_id' => $user->school_id,
            'name' => '2026-2027',
            'is_current' => true,
        ]);
        Teacher::create([
            'first_name' => 'Awa',
            'last_name' => 'Coulibaly',
            'monthly_salary' => 85000,
            'status' => 'actif',
            'school_year_id' => $schoolYear->id,
        ]);

        $this->actingAs($user)
            ->get(route('finances.expenses.create'))
            ->assertOk()
            ->assertSee('data-monthly-salary="85000"', false)
            ->assertSee("teacher.addEventListener('change', applyTeacherSalary)", false);
    }

    private function createSchoolOwner(): User
    {
        $owner = User::factory()->create();
        $school = School::create([
            'name' => 'École de test',
            'country' => 'Mali',
            'currency' => 'XOF',
            'institution_type' => 'private',
            'owner_user_id' => $owner->id,
        ]);

        $owner->school_id = $school->id;
        $owner->role = 'school_admin';
        $owner->save();

        return $owner;
    }

    private function createGradeBook(string $cycle): array
    {
        $user = $this->createSchoolOwner();
        $user->school->update(['cycles' => [$cycle]]);
        $year = SchoolYear::create([
            'school_id' => $user->school_id,
            'name' => '2026-2027',
            'is_current' => true,
        ]);
        $classRoom = ClassRoom::create([
            'name' => 'Classe test',
            'cycle' => $cycle,
            'monthly_fee' => 0,
            'school_year_id' => $year->id,
        ]);
        $student = Student::create([
            'registration_number' => 'GRADE-'.strtoupper($cycle),
            'first_name' => 'Awa',
            'last_name' => 'Diallo',
            'gender' => 'F',
            'birth_date' => '2015-04-01',
            'birth_place' => 'Bamako',
            'nationality' => 'Malienne',
            'inscription_date' => '2026-09-01',
            'class_name' => $classRoom->name,
            'class_room_id' => $classRoom->id,
            'parent_name' => 'Parent Diallo',
            'parent_phone' => '70000000',
            'school_year_id' => $year->id,
            'status' => 'actif',
        ]);

        $subjects = collect(['Mathématiques', 'Français'])->map(fn ($name, $index) => Subject::create([
            'name' => $name,
            'code' => 'GRADE-'.$index.'-'.strtoupper($cycle),
            'coefficient' => 1,
            'school_year_id' => $year->id,
        ]))->all();
        $teachers = collect(['Moussa', 'Aminata'])->map(fn ($firstName) => Teacher::create([
            'first_name' => $firstName,
            'last_name' => 'Traoré',
            'status' => 'actif',
            'school_year_id' => $year->id,
        ]))->all();

        return [$user, $year, $classRoom, $student, $subjects, $teachers];
    }
}
