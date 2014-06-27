<?php
namespace OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM;

use Doctrine\ORM\EntityRepository;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;

use Doctrine\ORM\EntityManager;

use Oro\Bundle\UserBundle\Entity\User;

use OroCRM\Bundle\AccountBundle\Entity\Account;
use OroCRM\Bundle\ChannelBundle\Entity\Channel;
use OroCRM\Bundle\ContactBundle\Entity\Contact;
use OroCRM\Bundle\SalesBundle\Entity\Opportunity;

class LoadOpportunitiesData extends AbstractFixture implements ContainerAwareInterface, DependentFixtureInterface
{
    const FLUSH_MAX = 50;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var User[]
     */
    protected $users;

    /**
     * @var Contact[]
     */
    protected $contacts;

    /** @var  EntityManager */
    protected $em;

    /**
     * {@inheritdoc}
     */
    public function getDependencies()
    {
        return [
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadContactData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadLeadsData',
            'OroCRM\Bundle\DemoDataBundle\Migrations\Data\Demo\ORM\LoadChannelData'
        ];
    }

     /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var Channel $channel */
        $channel = $this->container->get('doctrine.orm.entity_manager')->getRepository('OroCRMChannelBundle:Channel')
            ->findOneByName('default');

        if (!$channel) {
            throw new \Exception('"default" channel is not defined');
        }

        $this->initSupportingEntities($manager);
        $this->loadOpportunities($channel);
    }

    protected function initSupportingEntities(ObjectManager $manager = null)
    {
        if ($manager) {
            $this->em = $manager;
        }

        $this->users = $this->em->getRepository('OroUserBundle:User')->findAll();
        /** @var EntityRepository $repo */
        $repo = $this->em->getRepository('OroCRMContactBundle:Contact');
        $this->contacts = $repo->createQueryBuilder('contact')
            ->innerJoin('contact.accounts', 'account')
            ->getQuery()
            ->execute();
    }

    public function loadOpportunities(Channel $channel)
    {
        $randomUser = count($this->users) - 1;
        for ($i = 0; $i < 50; $i++) {
            $user = $this->users[mt_rand(0, $randomUser)];
            $this->setSecurityContext($user);
            $contact = $this->contacts[array_rand($this->contacts)];
            $opportunity = $this->createOpportunity($contact, $user, $channel);
            $this->persist($this->em, $opportunity);
            if ($i % self::FLUSH_MAX == 0) {
                $this->flush($this->em);
            }
        }
        $this->flush($this->em);
    }

    /**
     * @param User $user
     */
    protected function setSecurityContext($user)
    {
        $securityContext = $this->container->get('security.context');
        $token = new UsernamePasswordToken($user, $user->getUsername(), 'main');
        $securityContext->setToken($token);
    }

    /**
     * @param Contact $contact
     * @param User $user
     * @param Channel $channel
     *
     * @return Opportunity
     */
    protected function createOpportunity($contact, $user, $channel)
    {
        /** @var Account $account */
        $account = $contact->getAccounts()->first();
        $opportunity = new Opportunity();
        $opportunity->setName($account->getName());
        $opportunity->setContact($contact);
        $opportunity->setAccount($account);
        $opportunity->setOwner($user);
        $opportunity->setChannel($channel);

        return $opportunity;
    }

    /**
     * Persist object
     *
     * @param mixed $manager
     * @param mixed $object
     */
    private function persist($manager, $object)
    {
        $manager->persist($object);
    }

    /**
     * Flush objects
     *
     * @param mixed $manager
     */
    private function flush($manager)
    {
        $manager->flush();
    }
}
