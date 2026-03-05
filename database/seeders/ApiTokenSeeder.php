<?php

namespace Database\Seeders;

use App\Models\ApiToken;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApiTokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ApiToken::create([
            'name' => 'Tandes',
            'url_api' => 'https://ujiberkala-middle.kemenhub.go.id/api/v1/global',
            'token' => 'eyJpdiI6IjRJVTlHaUZFb0FFUnNCQVR1VXRtRXc9PSIsInZhbHVlIjoiRitGRDdvbVNTWTdrWFQvZ1FDWXhsR0tQeEkwZTA0ZGMvSGxQdm80YlJsYnVOYU9HYUZmYUxTL0N2eGl0QkJiSWdna1RUdHJZVVQxR1Qwa0FxUGt0U2xzNitYRVF4MCtLQ0hvQXQwdERyWEhjZXYwL3MzcUIvS05KTGFPcDlvT0MiLCJtYWMiOiJlOTFlOGM5M2ZmZmY1YTMxZTA0OGYyNDhiM2NkYzQyMDA3ZmU2ZmQyMDFiMzhiNTM1ZjJkZTE1MjYwYzM3MzQ4IiwidGFnIjoiIn0=',
            'is_active' => true,
        ]);

        ApiToken::create([
            'name' => 'Wiyung',
            'url_api' => 'https://ujiberkala-middle.kemenhub.go.id/api/v1/global',
            'token' => 'eyJpdiI6IlRleFpjNVByYkNQeWExbWNWWEZPZVE9PSIsInZhbHVlIjoiMTl4dEc2ayttbGFZR0NHRHI3ZWNzczA2YVZyeFN6NTYxSkdqNEI3cTczb1RlazI3dk9nSnA0VkkrajM4Q0NVWUJjeTlPWDQxd0p0cXJwSnZBN1krSC9kbUcrSmlHYk9HUlNQcHZORTBqaU1LcDVJWlFERkxyYzhlKzJmUmhCK3UiLCJtYWMiOiIyZDM0ODEzMzVmN2ExYjMxOWFkYWIxMmUzNmUwMDI2MTE0MzAyYWZjZTFlNjEzODUxZTIyY2Y4NzMwODcxYjE0In0=',
            'is_active' => false,
        ]);
    }
}
