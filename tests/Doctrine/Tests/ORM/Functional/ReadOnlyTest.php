<?php

declare(strict_types=1);

namespace Doctrine\Tests\ORM\Functional;

use Doctrine\ORM\Annotation as ORM;
use Doctrine\Tests\OrmFunctionalTestCase;

/**
 * Functional Query tests.
 *
 * @group DDC-692
 */
class ReadOnlyTest extends OrmFunctionalTestCase
{
    protected function setUp()
    {
        parent::setUp();

        try {
            $this->schemaTool->createSchema(
                [
                $this->em->getClassMetadata(ReadOnlyEntity::class),
                ]
            );
        } catch(\Exception $e) {
        }
    }

    public function testReadOnlyEntityNeverChangeTracked()
    {
        $readOnly = new ReadOnlyEntity("Test1", 1234);
        $this->em->persist($readOnly);
        $this->em->flush();

        $readOnly->name = "Test2";
        $readOnly->numericValue = 4321;

        $this->em->flush();
        $this->em->clear();

        $dbReadOnly = $this->em->find(ReadOnlyEntity::class, $readOnly->id);
        self::assertEquals("Test1", $dbReadOnly->name);
        self::assertEquals(1234, $dbReadOnly->numericValue);
    }

    /**
     * @group DDC-1659
     */
    public function testClearReadOnly()
    {
        $readOnly = new ReadOnlyEntity("Test1", 1234);
        $this->em->persist($readOnly);
        $this->em->flush();
        $this->em->getUnitOfWork()->markReadOnly($readOnly);

        $this->em->clear();

        self::assertFalse($this->em->getUnitOfWork()->isReadOnly($readOnly));
    }

    /**
     * @group DDC-1659
     */
    public function testClearEntitiesReadOnly()
    {
        // @TODO the semantics around `EntityManager#clear()` need to be improved
        $this->markTestIncomplete('Feature has been disabled, and will be re-enabled later on');
        $readOnly = new ReadOnlyEntity("Test1", 1234);
        $this->em->persist($readOnly);
        $this->em->flush();
        $this->em->getUnitOfWork()->markReadOnly($readOnly);

        $this->em->clear(get_class($readOnly));

        self::assertFalse($this->em->getUnitOfWork()->isReadOnly($readOnly));
    }
}

/**
 * @ORM\Entity(readOnly=true)
 */
class ReadOnlyEntity
{
    /**
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer")
     * @var int
     */
    public $id;
    /** @ORM\Column(type="string") */
    public $name;
    /** @ORM\Column(type="integer") */
    public $numericValue;

    public function __construct($name, $number)
    {
        $this->name = $name;
        $this->numericValue = $number;
    }
}
