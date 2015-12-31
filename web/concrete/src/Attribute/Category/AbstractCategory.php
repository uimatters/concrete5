<?php

namespace Concrete\Core\Attribute\Category;

use Concrete\Core\Application\Application;
use Concrete\Core\Attribute\Category\SearchIndexer\StandardSearchIndexerInterface;
use Concrete\Core\Attribute\EntityInterface;
use Concrete\Core\Attribute\Key\Factory;
use Concrete\Core\Attribute\Type;
use Concrete\Core\Attribute\TypeFactory;
use Concrete\Core\Entity\Attribute\Category;
use Concrete\Core\Entity\Attribute\Set;
use Concrete\Core\Entity\Attribute\Key\Key as AttributeKey;
use Doctrine\ORM\EntityManager;
use Concrete\Core\Entity\Attribute\Type as AttributeType;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractCategory implements CategoryInterface
{

    protected $entityManager;
    protected $entity;
    protected $categoryEntity;
    protected $application;

    /**
     * @return EntityRepository
     */
    abstract public function getAttributeRepository();

    public function getByID($akID)
    {
        return $this->getAttributeKeyByID($akID);
    }

    public function __construct(Application $application, EntityManager $entityManager)
    {
        $this->application = $application;
        $this->entityManager = $entityManager;
    }

    public function getList()
    {
        return $this->getAttributeRepository()->findAll();
    }

    public function getSearchableList()
    {
        $query = $this->getAttributeRepository()->createQueryBuilder('a');
        $query->join('a.attribute_key', 'ta');
        $query->andWhere('ta.akIsSearchable = true');
        return $query->getQuery()->getResult();
    }

    public function getSearchableIndexedList()
    {
        $query = $this->getAttributeRepository()->createQueryBuilder('a');
        $query->join('a.attribute_key', 'ta');
        $query->andWhere('ta.akIsSearchableIndexed = true');
        return $query->getQuery()->getResult();
    }

    public function getAttributeKeyByHandle($handle)
    {
        $query = $this->getAttributeRepository()->createQueryBuilder('a');
        $query->join('a.attribute_key', 'ta');
        $query->andWhere('ta.akHandle = :akHandle');
        $query->setParameter('akHandle', $handle);
        $attribute = $query->getQuery()->getOneOrNullResult();
        if ($attribute) {
            return $attribute->getAttributeKey();
        }
    }

    public function getAttributeKeyByID($akID)
    {
        $query = $this->getAttributeRepository()->createQueryBuilder('a');
        $query->join('a.attribute_key', 'ta');
        $query->andWhere('ta.akID = :akID');
        $query->setParameter('akID', $akID);
        $attribute = $query->getQuery()->getOneOrNullResult();
        if ($attribute) {
            return $attribute->getAttributeKey();
        }
    }

    // Create
    public function addFromRequest(AttributeType $type, Request $request)
    {
        $key = $type->getController()->createAttributeKey();
        $loader = $key->getRequestLoader();
        $loader->load($key, $request);

        // Modify the category's search indexer.
        $indexer = $this->getCategoryEntity()
            ->getController()->getSearchIndexer();
        $indexer->updateTable($this, $key);

        return $key;
    }

    public function import(AttributeType $type, \SimpleXMLElement $element)
    {
        $key = $type->getController()->createAttributeKey();
        $loader = $key->getImportLoader();
        $loader->load($key, $element);

        // Modify the category's search indexer.
        $indexer = $this->getCategoryEntity()
            ->getController()->getSearchIndexer();
        $indexer->updateTable($this, $key);

        return $key;
    }


    // Update
    public function updateFromRequest(AttributeKey $key, Request $request)
    {
        $loader = $key->getRequestLoader();
        $loader->load($key, $request);
        return $key;
    }


    /**
     * @return mixed
     */
    public function getCategoryEntity()
    {
        return $this->categoryEntity;
    }

    /**
     * @param mixed $categoryEntity
     */
    public function setCategoryEntity(Category $categoryEntity)
    {
        $this->categoryEntity = $categoryEntity;
    }

    public function setEntity(EntityInterface $entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return EntityInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }

    public function delete(AttributeKey $key)
    {
        $this->entityManager->remove($key);
    }

    public function associateAttributeKeyType(AttributeType $type)
    {
        $this->getCategoryEntity()->getAttributeTypes()->add($type);
        $this->entityManager->persist($this->getCategoryEntity());
        $this->entityManager->flush();
    }

    public function addSet($handle, $name, $pkg = null, $locked = null)
    {
        $set = new Set();
        $set->setAttributeKeyCategory($this->getCategoryEntity());
        $set->setAttributeSetHandle($handle);
        $set->setAttributeSetName($name);
        $set->setAttributeSetIsLocked($locked);
        $this->entityManager->persist($set);
        $this->entityManager->flush();
        return $set;
    }

    public function getSearchIndexer()
    {
        $indexer = $this->application->make('Concrete\Core\Attribute\Category\SearchIndexer\StandardSearchIndexer');
        return $indexer;
    }



}