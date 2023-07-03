<?php

namespace App\Controller\Blog\Admin;

use App\Controller\Admin\EntityCollection;
use App\Entity\Option;
use App\Repository\OptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\HiddenField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;

class OptionCrudController extends AbstractCrudController
{
    public function __construct(private OptionRepository $optionRepo)
    {
        
    }

    public static function getEntityFqcn(): string
    {
        return Option::class;
    }

    public function createIndexQueryBuilder(SearchDto $searchDto, EntityDto $entityDto, FieldCollection $fields, FilterCollection $filters): QueryBuilder
    {
        return $this->optionRepo->getIndexQueryBuilder();
    }

    public function index(AdminContext $context)
    {
        $response = parent::index($context);

        if($response instanceof Response)
            return $response;

        /** @var EntityCollection */
        $entities = $response->get('entities');

        foreach($entities as $entity)
        {
            $fields = $entity->getFields();
            $fields->unset($fields->getByProperty('type'));
        }

        return $response;
    }

    public function createEditForm(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormInterface
    {
        $formBuilder = parent::createEditForm($entityDto, $formOptions, $context);

        /** @var Option */
        $viewData = $formBuilder->getViewData();
        $value = $viewData->getValue();
        
        $formBuilder->add('value', $viewData->getType(), [
            'data' => $viewData->getType() === CheckboxType::class ? boolval($value) : $value,
            'label' => 'Valeur',
            'required' => false
        ]);

        return $formBuilder;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        /** @var Option */
        $option = $entityInstance;

        if($option->getType() === CheckboxType::class)
        {
            $value = $option->getValue() ? '1' : '0';
            $option->setValue($value);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->remove(Crud::PAGE_INDEX, Action::BATCH_DELETE)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->remove(Crud::PAGE_INDEX, Action::NEW);
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityPermission('ROLE_ADMIN')
            ->setSearchFields(null)
            ->setEntityLabelInPlural('Réglages généraux')
            ->showEntityActionsInlined();
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('label', 'Option')
            ->setFormTypeOption('attr', [
                'readonly' => true
            ]);

        yield TextField::new('value', 'Valeur');
        yield HiddenField::new('type');
    }
}
