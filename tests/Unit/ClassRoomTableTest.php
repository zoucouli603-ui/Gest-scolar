<?php

namespace Tests\Unit;

use App\Models\ClassRoom;
use PHPUnit\Framework\TestCase;

class ClassRoomTableTest extends TestCase
{
    public function test_classroom_uses_the_correct_database_table(): void
    {
        $classRoom = new ClassRoom();

        $this->assertSame('class_rooms', $classRoom->getTable());
    }
}
