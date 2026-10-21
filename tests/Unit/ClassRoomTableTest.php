<?php

namespace Tests\Unit;

use App\Models\ClassRoom;
use App\Models\Student;
use PHPUnit\Framework\TestCase;

class ClassRoomTableTest extends TestCase
{
    public function test_classroom_uses_the_correct_database_table(): void
    {
        $classRoom = new ClassRoom();

        $this->assertSame('class_rooms', $classRoom->getTable());
    }

    public function test_state_matricule_is_used_only_for_the_specified_lycee_classes(): void
    {
        foreach (Student::STATE_MATRICULE_CARD_CLASSES as $className) {
            $student = new Student(['class_name' => $className]);
            $classRoom = new ClassRoom(['name' => $className]);
            $classRoom->cycle = 'Lycée';
            $student->setRelation('classRoom', $classRoom);

            $this->assertTrue($student->usesStateMatriculeOnCard(), $className);
        }

        foreach (['Terminale', 'TSE A', 'TSE'] as $className) {
            $student = new Student(['class_name' => $className]);
            $classRoom = new ClassRoom(['name' => $className]);
            $classRoom->cycle = '2è Cycle';
            $student->setRelation('classRoom', $classRoom);

            $this->assertFalse($student->usesStateMatriculeOnCard(), $className);
        }
    }
}
