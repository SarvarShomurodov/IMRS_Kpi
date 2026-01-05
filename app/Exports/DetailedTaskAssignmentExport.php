<?php
namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Carbon\Carbon;

class DetailedTaskAssignmentExport implements FromCollection, WithEvents
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        return collect([]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Setup date range
                if (!$this->startDate || !$this->endDate) {
                    $today = Carbon::now();
                    $currentMonth26 = Carbon::create($today->year, $today->month, 26);
                    $lastMonth25 = Carbon::create($today->year, $today->month - 1, 25);
                    
                    if ($today->day < 26) {
                        $currentMonth26 = $currentMonth26->subMonth();
                        $lastMonth25 = $lastMonth25->subMonth();
                    }
                    
                    $this->startDate = $lastMonth25->format('Y-m-d');
                    $this->endDate = $currentMonth26->format('Y-m-d');
                }

                // Header
                $sheet->setCellValue('A1', '#');
                $sheet->setCellValue('B1', 'Subtask nomi');
                $sheet->setCellValue('C1', 'Vazifalar');
                $sheet->setCellValue('D1', 'Ball (Rating)');
                $sheet->setCellValue('E1', 'Izoh');
                $sheet->setCellValue('F1', 'Sana');

                // Header styling
                $sheet->getStyle('A1:F1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'E8E8E8']
                    ],
                    'alignment' => [
                        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                $currentRow = 2;

                // Get users and their assignments
                $users = User::whereHas('taskAssignments', function($query) {
                    $query->whereBetween('addDate', [$this->startDate, $this->endDate]);
                })->with(['taskAssignments' => function($query) {
                    $query->whereBetween('addDate', [$this->startDate, $this->endDate])
                          ->with(['subtask.task']);
                }])->get();

                foreach ($users as $user) {
                    // Empty row before user
                    $currentRow++;
                    
                    // User name row - firstName va lastName birlashtirish
                    $userName = trim(($user->firstName ?? '') . ' ' . ($user->lastName ?? ''));
                    if (empty($userName)) {
                        $userName = 'User #' . $user->id; // fallback
                    }
                    
                    $sheet->setCellValue('C' . $currentRow, $userName);
                    $sheet->getStyle('A' . $currentRow . ':F' . $currentRow)->applyFromArray([
                        'font' => ['bold' => true, 'size' => 11],
                        'fill' => [
                            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'D4EDDA']
                        ]
                    ]);
                    $currentRow++;

                    // User's assignments
                    $index = 1;
                    foreach ($user->taskAssignments as $assignment) {
                        $subtaskInfo = $assignment->subtask->title . ' (' . $assignment->subtask->min . ' - ' . $assignment->subtask->max . ')';
                        
                        $sheet->setCellValue('A' . $currentRow, $index);
                        $sheet->setCellValue('B' . $currentRow, $subtaskInfo);
                        $sheet->setCellValue('C' . $currentRow, $assignment->subtask->task->taskName);
                        $sheet->setCellValue('D' . $currentRow, $assignment->rating);
                        $sheet->setCellValue('E' . $currentRow, $assignment->comment);
                        $sheet->setCellValue('F' . $currentRow, Carbon::parse($assignment->addDate)->format('n/j/Y'));
                        
                        $currentRow++;
                        $index++;
                    }
                }

                // Column widths
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(50);
                $sheet->getColumnDimension('C')->setWidth(40);
                $sheet->getColumnDimension('D')->setWidth(15);
                $sheet->getColumnDimension('E')->setWidth(60);
                $sheet->getColumnDimension('F')->setWidth(12);

                // Wrap text for long columns
                $sheet->getStyle('B:E')->getAlignment()->setWrapText(true);
            }
        ];
    }
}