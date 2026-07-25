<?php

namespace Database\Factories;

use App\Models\Person;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'workspace_id' => Workspace::factory(),
            'uploaded_by' => Person::factory(),
            'name' => $this->faker->word() . '.' . $this->faker->fileExtension(),
            'path' => '/storage/' . $this->faker->word() . '/' . $this->faker->uuid() . '.tmp',
            'mime_type' => $this->faker->mimeType(),
            'size' => $this->faker->numberBetween(1024, 10485760),
        ];
    }
}