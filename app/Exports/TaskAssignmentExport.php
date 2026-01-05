<?php

namespace App\Exports;

use App\Models\TaskAssignment;
use App\Models\Assignment;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class TaskAssignmentExport implements FromArray, WithStyles, WithColumnWidths
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function array(): array
    {
        try {
            // Date range setup
            if (!$this->startDate || !$this->endDate) {
                $today = Carbon::now();

                if ($today->day < 26) {
                    // O'tgan oyning 26-si
                    $this->startDate = Carbon::create($today->year, $today->month, 1)->subMonth()->day(26)->format('Y-m-d');
                    // Hozirgi oyning 25-si
                    $this->endDate = Carbon::create($today->year, $today->month, 25)->format('Y-m-d');
                } else {
                    // Hozirgi oyning 26-si
                    $this->startDate = Carbon::create($today->year, $today->month, 26)->format('Y-m-d');
                    // Keyingi oyning 25-si
                    $this->endDate = Carbon::create($today->year, $today->month, 1)->addMonth()->day(25)->format('Y-m-d');
                }
            }

            // Header row
            $data = [['#', 'Subtask nomi', 'Vazifalar', 'Ball (Rating)', 'Izoh', 'Sana']];

            // ✅ 1. BARCHA userlarni olish
            $users = User::withTrashed()
                ->where(function ($query) {
                    $query->whereNull('deleted_at')
                        ->orWhere('deleted_at', '>', $this->startDate);
                })
                ->whereDoesntHave('roles', function ($query) {
                    $query->whereIn('name', ['Admin', 'Super Admin', 'Texnik']);
                })
                ->get();

            $userIds = $users->pluck('id');

            // ✅ 2. ESKI MA'LUMOTLAR - TaskAssignment dan
            $oldAssignments = TaskAssignment::with([
                'subtask.task',
                'project',
                'user' => function ($query) {
                    $query->withTrashed();
                },
            ])
                ->whereIn('user_id', $userIds)
                ->whereBetween('addDate', [$this->startDate, $this->endDate])
                ->get();

            Log::info('Export Detailed - Old Assignments:', ['count' => $oldAssignments->count()]);

            // ✅ 3. YANGI MA'LUMOTLAR - Assignment dan
            $newAssignments = Assignment::with([
                'task',
                'subtask',
                'user' => function ($query) {
                    $query->withTrashed();
                },
            ])
                ->whereIn('user_id', $userIds)
                ->whereNotNull('task_id')
                ->whereNotNull('subtask_id')
                ->whereNotNull('rating')
                ->whereBetween('date', [$this->startDate, $this->endDate])
                ->get();

            Log::info('Export Detailed - New Assignments:', ['count' => $newAssignments->count()]);

            // ✅ 4. IKKALASINI BIRLASHTIRISH
            $normalizedNewAssignments = $newAssignments->map(function ($assignment) {
                return (object) [
                    'id' => $assignment->id,
                    'user_id' => $assignment->user_id,
                    'rating' => (float) $assignment->rating,
                    'comment' => $assignment->comment,
                    'addDate' => $assignment->date,  // ✅ date → addDate
                    'subtask' => (object) [
                        'id' => $assignment->subtask_id,
                        'title' => $assignment->subtask->title ?? 'N/A',
                        'min' => $assignment->subtask->min ?? 0,
                        'max' => $assignment->subtask->max ?? 0,
                        'task' => (object) [
                            'id' => $assignment->task_id,
                            'taskName' => $assignment->task->taskName ?? 'N/A',
                        ],
                    ],
                    'project' => (object) [
                        'name' => $assignment->user->project->name ?? 'N/A',
                    ],
                    'user' => $assignment->user,
                ];
            });

            $allAssignments = $oldAssignments->concat($normalizedNewAssignments)
                ->sortBy([
                    ['user_id', 'asc'],
                    ['addDate', 'asc']
                ]);

            Log::info('Export Detailed - All Assignments:', [
                'total' => $allAssignments->count(),
                'old' => $oldAssignments->count(),
                'new' => $normalizedNewAssignments->count()
            ]);

            $groupedAssignments = $allAssignments->groupBy('user_id');

            // ✅ 5. Excel uchun ma'lumotlar tayyorlash
            foreach ($groupedAssignments as $userId => $userAssignments) {
                $user = $userAssignments->first()->user;

                if (!$user) {
                    continue; // User topilmasa skip
                }

                // User header row (bo'sh qator)
                $data[] = ['', '', '', '', '', ''];

                // User name row
                $userName = trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? ''));
                if (empty($userName)) {
                    $userName = 'User #' . $userId;
                }

                // O'chirilgan user ekanligini ko'rsatish
                if ($user->deleted_at) {
                    $userName .= ' (O\'chirilgan)';
                }

                $data[] = ['', '', $userName, '', '', ''];

                // Task assignments
                $index = 1;
                foreach ($userAssignments as $assignment) {
                    if (!$assignment->subtask || !$assignment->subtask->task) {
                        continue;
                    }

                    $subtaskInfo = $assignment->subtask->title . 
                        ' (' . $assignment->subtask->min . ' - ' . $assignment->subtask->max . ')';
                    $taskName = $assignment->subtask->task->taskName;

                    $data[] = [
                        $index,
                        $subtaskInfo,
                        $taskName,
                        $assignment->rating,
                        $assignment->comment ?? '',
                        Carbon::parse($assignment->addDate)->format('n/j/Y')
                    ];
                    $index++;
                }
            }

            return $data;
        } catch (\Exception $e) {
            Log::error('Export Detailed Error:', ['error' => $e->getMessage()]);
            return [['Xatolik: ' . $e->getMessage()]];
        }
    }

    public function styles(Worksheet $sheet)
    {
        $rowCount = $sheet->getHighestRow();

        // Header style
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E8E8E8'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // User name rows styling
        for ($row = 2; $row <= $rowCount; $row++) {
            $cellValue = $sheet->getCell('A' . $row)->getValue();
            $cellC = $sheet->getCell('C' . $row)->getValue();

            // User name qatori ekanligini tekshirish
            if (empty($cellValue) && !empty($cellC) && 
                empty($sheet->getCell('B' . $row)->getValue()) && 
                empty($sheet->getCell('D' . $row)->getValue())) {
                
                // O'chirilgan userlar uchun qizil rang
                $isDeleted = strpos($cellC, '(O\'chirilgan)') !== false;

                $sheet->getStyle('A' . $row . ':F' . $row)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => $isDeleted ? 'FFB6B6' : 'D4EDDA'],
                    ],
                ]);
            }
        }

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 50,
            'C' => 40,
            'D' => 15,
            'E' => 60,
            'F' => 12,
        ];
    }
}