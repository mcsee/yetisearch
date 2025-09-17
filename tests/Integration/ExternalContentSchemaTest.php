<?php

namespace YetiSearch\Tests\Integration;

use YetiSearch\Tests\TestCase;
use YetiSearch\Geo\GeoPoint;

class ExternalContentSchemaTest extends TestCase
{
    private function getPdo($search): \PDO
    {
        $ref = new \ReflectionClass($search);
        $m = $ref->getMethod('getStorage');
        $m->setAccessible(true);
        $storage = $m->invoke($search);
        $sref = new \ReflectionClass($storage);
        $p = $sref->getProperty('connection');
        $p->setAccessible(true);
        return $p->getValue($storage);
    }

    private function getCreateSql(\PDO $pdo, string $table): ?string
    {
        $stmt = $pdo->prepare("SELECT sql FROM sqlite_master WHERE type='table' AND name=?");
        $stmt->execute([$table]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $row['sql'] ?? null;
    }

    public function test_schema_uses_doc_id_and_content_rowid(): void
    {
        $search = $this->createSearchInstance([
            'storage' => ['external_content' => true],
        ]);
        $index = 'ext_schema_idx';
        $this->createTestIndex($index);

        $pdo = $this->getPdo($search);
        $mainSql = $this->getCreateSql($pdo, $index);
        $ftsSql = $this->getCreateSql($pdo, $index . '_fts');

        $this->assertNotEmpty($mainSql, 'Main table not created');
        $this->assertStringContainsString('doc_id INTEGER PRIMARY KEY', $mainSql);
        $this->assertStringContainsString('id TEXT UNIQUE', $mainSql);

        $this->assertNotEmpty($ftsSql, 'FTS table not created');
        $this->assertStringContainsString("USING fts5", $ftsSql);
        // External-content FTS should reference content table + rowid
        $this->assertStringContainsString("content='" . $index . "'", str_replace('"', "'", $ftsSql));
        $this->assertStringContainsString("content_rowid='doc_id'", str_replace('"', "'", $ftsSql));
    }

    public function test_basic_search_in_external_mode(): void
    {
        $search = $this->createSearchInstance([
            'storage' => ['external_content' => true],
            'indexer' => [
                'fields' => [
                    'title' => ['boost' => 3.0, 'store' => true],
                    'content' => ['boost' => 1.0, 'store' => true],
                ],
                'fts' => [ 'multi_column' => true ],
            ],
        ]);
        $index = 'ext_basic_idx';
        $this->createTestIndex($index);

        $docs = [
            ['id' => 'd1', 'content' => ['title' => 'Hello World', 'content' => 'First doc']],
            ['id' => 'd2', 'content' => ['title' => 'Another', 'content' => 'Hello again']],
        ];
        $search->indexBatch($index, $docs);
        $search->getIndexer($index)->flush();

        $res = $search->search($index, 'hello', ['limit' => 10]);
        $this->assertGreaterThanOrEqual(2, $res['total']);
        $ids = array_column($res['results'], 'id');
        $this->assertContains('d1', $ids);
        $this->assertContains('d2', $ids);
    }
}
