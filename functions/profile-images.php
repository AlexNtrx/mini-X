<?php

final class ProfileImageChanges
{
    private array $previousFiles = [];
    private array $newFiles = [];

    public function record(?string $previous, ?string $replacement): void
    {
        if ($previous !== null) {
            $this->previousFiles[] = $previous;
        }
        if ($replacement !== null) {
            $this->newFiles[] = $replacement;
        }
    }

    public function finish(bool $saved): void
    {
        foreach ($saved ? $this->previousFiles : $this->newFiles as $path) {
            if (is_file($path) && !@unlink($path)) {
                error_log("Could not remove profile image: " . $path);
            }
        }
    }
}

function getLocalProfileImagePath(string $directory, ?string $filename): ?string
{
    if (!$filename || preg_match('/^https?:\/\//i', $filename)) {
        return null;
    }
    return $directory . basename($filename);
}

function saveProfileImageChange(?ProfileImageChanges $pending, ?string $previous, ?string $replacement, callable $save): bool
{
    $changes = $pending ?? new ProfileImageChanges();
    $changes->record($previous, $replacement);
    $saved = false;
    try {
        $saved = $save();
        return $saved;
    } finally {
        if ($pending === null) {
            $changes->finish($saved);
        }
    }
}
