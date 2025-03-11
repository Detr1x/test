<?php

namespace App\Exports;

use App\Models\Columns;
use App\Models\Column_Data;
use App\Models\Tables;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TableExport implements FromArray, WithHeadings
{
    protected $tableToken;

    public function __construct($tableToken)
    {
        $this->tableToken = $tableToken;
    }

    /**
     * Заголовки (первые строки Excel)
     */
    public function headings(): array
    {
        return Columns::where('table_token', $this->tableToken)
            ->orderBy('s_number')
            ->pluck('name')
            ->toArray();
    }

    /**
     * Данные таблицы
     */
    public function array(): array
    {
        $columns = Columns::where('table_token', $this->tableToken)
            ->orderBy('s_number')
            ->pluck('column_token')
            ->toArray();

        $dataRows = Column_Data::where('table_token', $this->tableToken)
            ->orderBy('s_number') // Группировка строк
            ->get()
            ->groupBy('s_number');

        $exportData = [];

        foreach ($dataRows as $row) {
            $rowData = [];

            foreach ($columns as $columnToken) {
                $cell = $row->firstWhere('column_token', $columnToken);
                $rowData[] = $cell->data ?? ''; // Заполняем пустыми значениями, если данных нет
            }

            $exportData[] = $rowData;
        }

        return $exportData;
    }
}
