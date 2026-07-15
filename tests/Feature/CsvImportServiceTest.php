<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\Income;
use App\Models\User;
use App\Services\CsvImportService;
use Illuminate\Http\UploadedFile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsvImportServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CsvImportService $service;
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CsvImportService();
        $this->user = User::factory()->create();
    }

    // ─── Parse ───────────────────────────────────────────────────

    public function test_parse_returns_headers_and_rows(): void
    {
        $csv = "date,amount,category,description\n2026-01-15,1500,Salary,January pay\n2026-01-16,200,Food,Lunch";
        $file = UploadedFile::fake()->createWithContent('test.csv', $csv);

        $result = $this->service->parse($file);

        $this->assertEquals(['date', 'amount', 'category', 'description'], $result['headers']);
        $this->assertCount(2, $result['rows']);
        $this->assertEquals(2, $result['total']);
        $this->assertEquals('January pay', $result['rows'][0]['description']);
    }

    public function test_parse_handles_empty_csv(): void
    {
        $csv = "date,amount\n";
        $file = UploadedFile::fake()->createWithContent('empty.csv', $csv);

        $result = $this->service->parse($file);

        $this->assertEquals(['date', 'amount'], $result['headers']);
        $this->assertEmpty($result['rows']);
        $this->assertEquals(0, $result['total']);
    }

    public function test_parse_skips_mismatched_rows(): void
    {
        $csv = "date,amount,category\n2026-01-15,1500,Salary\nbad,row";
        $file = UploadedFile::fake()->createWithContent('bad.csv', $csv);

        $result = $this->service->parse($file);

        // The mismatched row should be skipped
        $this->assertCount(1, $result['rows']);
        $this->assertEquals(1, $result['total']);
    }

    // ─── Preview ─────────────────────────────────────────────────

    public function test_preview_returns_mapped_sample(): void
    {
        $csv = "date,amount,category,note\n2026-01-15,1500,Salary,January\n2026-01-16,-200,Food,Lunch\n2026-01-17,3000,Freelance,Project\n2026-01-18,-50,Transport,Bus\n2026-01-19,-100,Food,Dinner\n2026-01-20,500,Gift,Birthday";
        $file = UploadedFile::fake()->createWithContent('preview.csv', $csv);

        $mapping = [
            'date' => 'date',
            'amount' => 'amount',
            'category' => 'category',
            'description' => 'note',
        ];

        $result = $this->service->preview($file, $mapping);

        $this->assertEquals(['date', 'amount', 'category', 'note'], $result['headers']);
        $this->assertCount(5, $result['sample']); // max 5 preview rows
        $this->assertEquals(6, $result['total']);
        $this->assertEquals('January', $result['sample'][0]['description']);
    }

    // ─── Import ──────────────────────────────────────────────────

    public function test_import_creates_income_for_positive_amounts(): void
    {
        $csv = "date,amount,category\n2026-01-15,5000,Salary\n2026-01-16,2000,Freelance";
        $file = UploadedFile::fake()->createWithContent('income.csv', $csv);

        $mapping = ['date' => 'date', 'amount' => 'amount', 'category' => 'category'];

        $result = $this->service->import($this->user->id, $file, $mapping);

        $this->assertEquals(2, $result['imported']);
        $this->assertEmpty($result['errors']);
        $this->assertEquals(2, $result['total']);

        $this->assertDatabaseHas('incomes', [
            'user_id' => $this->user->id,
            'amount' => 5000,
            'category' => 'Salary',
        ]);
        $this->assertDatabaseHas('incomes', [
            'user_id' => $this->user->id,
            'amount' => 2000,
            'category' => 'Freelance',
        ]);
    }

    public function test_import_creates_expense_for_negative_amounts(): void
    {
        $csv = "date,amount,category\n2026-01-15,-1500,Rent\n2026-01-16,-200,Food";
        $file = UploadedFile::fake()->createWithContent('expenses.csv', $csv);

        $mapping = ['date' => 'date', 'amount' => 'amount', 'category' => 'category'];

        $result = $this->service->import($this->user->id, $file, $mapping);

        $this->assertEquals(2, $result['imported']);
        $this->assertEmpty($result['errors']);

        $this->assertDatabaseHas('expenses', [
            'user_id' => $this->user->id,
            'amount' => 1500, // stored as absolute value
            'category' => 'Rent',
        ]);
        $this->assertDatabaseHas('expenses', [
            'user_id' => $this->user->id,
            'amount' => 200,
            'category' => 'Food',
        ]);
    }

    public function test_import_handles_currency_symbols_in_amount(): void
    {
        $csv = "date,amount\n2026-01-15,\"\$1,500.00\"\n2026-01-16,€200.50";
        $file = UploadedFile::fake()->createWithContent('currency.csv', $csv);

        $mapping = ['date' => 'date', 'amount' => 'amount'];

        $result = $this->service->import($this->user->id, $file, $mapping);

        $this->assertEquals(2, $result['imported']);

        $this->assertDatabaseHas('incomes', [
            'user_id' => $this->user->id,
            'amount' => 1500.00,
        ]);
        $this->assertDatabaseHas('incomes', [
            'user_id' => $this->user->id,
            'amount' => 200.50,
        ]);
    }

    public function test_import_reports_missing_amount_or_date(): void
    {
        $csv = "date,amount\n2026-01-15,\n,500";
        $file = UploadedFile::fake()->createWithContent('missing.csv', $csv);

        $mapping = ['date' => 'date', 'amount' => 'amount'];

        $result = $this->service->import($this->user->id, $file, $mapping);

        $this->assertEquals(0, $result['imported']);
        $this->assertCount(2, $result['errors']);
        $this->assertEquals('Row 1: Missing amount or date', $result['errors'][0]);
        $this->assertEquals('Row 2: Missing amount or date', $result['errors'][1]);
    }

    public function test_import_without_headers(): void
    {
        $csv = "2026-01-15,5000,Salary\n2026-01-16,-200,Food";
        $file = UploadedFile::fake()->createWithContent('noheader.csv', $csv);

        $mapping = ['date' => '0', 'amount' => '1', 'category' => '2'];

        $result = $this->service->import($this->user->id, $file, $mapping, false);

        $this->assertEquals(2, $result['imported']);
        $this->assertEmpty($result['errors']);

        $this->assertDatabaseHas('incomes', ['amount' => 5000]);
        $this->assertDatabaseHas('expenses', ['amount' => 200]);
    }

    public function test_import_with_description(): void
    {
        $csv = "date,amount,category,description\n2026-01-15,1000,Gift,Birthday present for mom";
        $file = UploadedFile::fake()->createWithContent('desc.csv', $csv);

        $mapping = [
            'date' => 'date',
            'amount' => 'amount',
            'category' => 'category',
            'description' => 'description',
        ];

        $result = $this->service->import($this->user->id, $file, $mapping);

        $this->assertEquals(1, $result['imported']);
        $this->assertDatabaseHas('incomes', [
            'description' => 'Birthday present for mom',
        ]);
    }

    public function test_import_handles_empty_csv(): void
    {
        $csv = "date,amount\n";
        $file = UploadedFile::fake()->createWithContent('empty.csv', $csv);

        $mapping = ['date' => 'date', 'amount' => 'amount'];

        $result = $this->service->import($this->user->id, $file, $mapping);

        $this->assertEquals(0, $result['imported']);
        $this->assertEquals(0, $result['total']);
    }
}
