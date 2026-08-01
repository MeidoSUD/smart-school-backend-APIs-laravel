<?php
/**
 * Fix all migration index/foreign/unique names exceeding MySQL's 64-char limit.
 */
$dir = __DIR__ . '/database/migrations';
$files = glob($dir . '/*.php');
$fixed = 0;
$totalIssues = 0;

function shortenName($name) {
    if (strlen($name) <= 64) return $name;
    // Use a hash of the original name to ensure uniqueness
    $hash = substr(md5($name), 0, 8);
    // Truncate and append hash
    $truncated = substr($name, 0, 55) . '_' . $hash;
    return substr($truncated, 0, 64);
}

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;

    // Extract table name
    if (!preg_match("/Schema::create\(\s*['\"]([^'\"]+)['\"]/", $content, $tableMatch)) {
        continue;
    }
    $tableName = $tableMatch[1];
    $issues = [];

    // 1. Fix explicit index names: ->index('column', 'index_name')
    // Pattern: ->index('col_name', 'explicit_index_name')
    $content = preg_replace_callback(
        '/->index\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*\)/',
        function($m) use ($tableName, &$issues) {
            $newName = shortenName($m[2]);
            if ($newName !== $m[2]) {
                $issues[] = "  Index '$m[2]' (" . strlen($m[2]) . " chars) -> '$newName' (" . strlen($newName) . " chars)";
            }
            return "->index('{$m[1]}', '{$newName}')";
        },
        $content
    );

    // 2. Fix auto-generated index names: ->index() with no name
    // Pattern: $table->type('col')->index()
    $content = preg_replace_callback(
        '/(\$table->\w+\([\'"]([^\'"]+)[\'"]\))->index\(\)/',
        function($m) use ($tableName, &$issues) {
            $colName = $m[2];
            $autoName = $tableName . '_' . $colName . '_index';
            if (strlen($autoName) > 64) {
                $newName = shortenName($autoName);
                $issues[] = "  Auto-index '$autoName' (" . strlen($autoName) . " chars) -> '$newName' (" . strlen($newName) . " chars)";
                return $m[1] . "->index('{$newName}')";
            }
            return $m[0];
        },
        $content
    );

    // 3. Fix explicit foreign key names: ->foreign('col', 'fk_name')
    $content = preg_replace_callback(
        '/->foreign\(\s*[\'"]([^\'"]+)[\'"]\s*,\s*[\'"]([^\'"]+)[\'"]\s*\)/',
        function($m) use ($tableName, &$issues) {
            $newName = shortenName($m[2]);
            if ($newName !== $m[2]) {
                $issues[] = "  FK '$m[2]' (" . strlen($m[2]) . " chars) -> '$newName' (" . strlen($newName) . " chars)";
            }
            return "->foreign('{$m[1]}', '{$newName}')";
        },
        $content
    );

    // 4. Fix auto-generated foreign key names: ->foreign('col') without explicit name
    // This is tricky because ->foreign('col') may be followed by ->references(...)->on(...)
    // The auto name is: {table}_{col}_foreign
    $content = preg_replace_callback(
        '/->foreign\(\s*[\'"]([^\'"]+)[\'"]\s*\)(?!\s*,)/',
        function($m) use ($tableName, &$issues) {
            $colName = $m[1];
            $autoName = $tableName . '_' . $colName . '_foreign';
            if (strlen($autoName) > 64) {
                $newName = shortenName($autoName);
                $issues[] = "  Auto-FK '$autoName' (" . strlen($autoName) . " chars) -> '$newName' (" . strlen($newName) . " chars)";
                return "->foreign('{$colName}', '{$newName}')";
            }
            return $m[0];
        },
        $content
    );

    // 5. Fix auto-generated unique names: $table->type('col')->unique()
    $content = preg_replace_callback(
        '/(\$table->\w+\([\'"]([^\'"]+)[\'"]\))->unique\(\)/',
        function($m) use ($tableName, &$issues) {
            $colName = $m[2];
            $autoName = $tableName . '_' . $colName . '_unique';
            if (strlen($autoName) > 64) {
                $newName = shortenName($autoName);
                $issues[] = "  Auto-unique '$autoName' (" . strlen($autoName) . " chars) -> '$newName' (" . strlen($newName) . " chars)";
                return $m[1] . "->unique('{$newName}')";
            }
            return $m[0];
        },
        $content
    );

    // 6. Fix explicit unique names: ->unique('explicit_name')
    $content = preg_replace_callback(
        '/->unique\(\s*[\'"]([^\'"]+)[\'"]\s*\)/',
        function($m) use ($tableName, &$issues) {
            $newName = shortenName($m[1]);
            if ($newName !== $m[1]) {
                $issues[] = "  Unique '$m[1]' (" . strlen($m[1]) . " chars) -> '$newName' (" . strlen($newName) . " chars)";
            }
            return "->unique('{$newName}')";
        },
        $content
    );

    if ($content !== $original) {
        file_put_contents($file, $content);
        $basename = basename($file);
        echo "FIXED: $basename\n";
        foreach ($issues as $issue) {
            echo "$issue\n";
        }
        $fixed++;
        $totalIssues += count($issues);
    }
}

echo "\nDone. Fixed $fixed files, $totalIssues index name issues.\n";
