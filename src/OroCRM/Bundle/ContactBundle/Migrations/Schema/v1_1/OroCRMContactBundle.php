<?php

namespace OroCRM\Bundle\ContactBundle\Migrations\Schema\v1_1;

use Doctrine\DBAL\Schema\Schema;
use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtension;
use Oro\Bundle\MigrationBundle\Migration\Extension\RenameExtensionAwareInterface;

class OroCRMContactBundle extends Migration implements RenameExtensionAwareInterface
{
    /**
     * @var RenameExtension
     */
    protected $renameExtension;

    /**
     * @inheritdoc
     */
    public function setRenameExtension(RenameExtension $renameExtension)
    {
        $this->renameExtension = $renameExtension;
    }

    /**
     * @inheritdoc
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        $queries->addQuery(
            $this->renameExtension->getRenameTableQuery(
                'orocrm_contact_to_contact_group',
                'orocrm_contact_to_contact_grp'
            )
        );
        $queries->addQuery(
            $this->renameExtension->getRenameTableQuery(
                'orocrm_contact_address_to_address_type',
                'orocrm_contact_adr_to_adr_type'
            )
        );
    }
}
