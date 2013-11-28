<?php
namespace Neducatio\TestBundle\Tests\Features\Context;

use Neducatio\TestBundle\Tests\Features\Context\FakeContext\TestableBaseSubContext;

use Mockery as m;

/**
 * Tests
 *
 * @covers Neducatio\TestBundle\Features\Context\BaseSubContext
 */
class BaseSubContextTest extends SubContextTestCase
{
  private $translator;
  private $feature;
  private $registry;
  private $builder;

  /**
   * Sets up
   */
  public function setUp()
  {
    $this->translator = m::mock('Symfony\Bundle\FrameworkBundle\Translation\Translator');
    $this->builder = m::mock('Neducatio\TestBundle\PageObject\PageObjectBuilder');
    $this->builder->shouldReceive('build')->andReturn('page');
    $kernel = $this->getKernelMock();
    $this->registry = m::mock('Neducatio\TestBundle\Utility\Reqistry');
    $this->feature = new TestableBaseSubContext($kernel);
    $this->feature->setBuilder($this->builder);
  }

  /**
   * Construct test
   *
   * @test
   */
  public function __construct_shouldCreateInstanceOf()
  {
    $this->assertInstanceOf('Neducatio\TestBundle\Features\Context\BaseSubContext', $this->feature);
  }

  /**
   * set builder test
   *
   * @test
   */
  public function setBuilder_builderShouldBeAvailableinContext()
  {
    $builder = m::mock('Neducatio\TestBundle\PageObject\PageObjectBuilder');
    $builder->shouldReceive('build')->andReturn('page');
    $kernel = $this->getKernelMock();
    $feature = new TestableBaseSubContext($kernel);
    $this->assertNull($feature->builder);
    $feature->setBuilder($builder);
    $this->assertNotNull($feature->builder);
  }

  /**
   * get & set Page test
   *
   * @test
   */
  public function getPage_pageIsSet_shouldReturnGivenPage()
  {
    $pageObject = new \stdClass();
    $this->feature->setParentContext($this->getParentContextMock());
    $this->feature->getMainContext()->getRegistry()->shouldReceive('get')->andReturn($pageObject);
    $this->assertSame($pageObject, $this->feature->getPage());
  }
  /**
   * access page test
   *
   * @test
   */
  public function page_noParameters_shouldUseRegistryAccessToRetrievePage()
  {
    $pageObject = m::mock('\Neducatio\TestBundle\PageObject\BasePageObject');
    $this->feature->setParentContext($this->getParentContextMock());
    $this->feature->getMainContext()->getRegistry()
        ->shouldReceive('access')->once()
        ->with(TestableBaseSubContext::PAGE_KEY)
        ->andReturn($pageObject);

    $this->assertSame($pageObject, $this->feature->page());
  }
  /**
   * access page test
   *
   * @test
   */
  public function page_pageObjectPassed_shouldUseRegistryAccessToSetPage()
  {
    $pageObject = m::mock('\Neducatio\TestBundle\PageObject\BasePageObject');
    $this->feature->setParentContext($this->getParentContextMock());
    $this->feature->getMainContext()->getRegistry()
        ->shouldReceive('access')->once()
        ->with(TestableBaseSubContext::PAGE_KEY, $pageObject)
        ->andReturn($pageObject);

    $this->assertSame($pageObject, $this->feature->page($pageObject));
  }

  /**
   * @test
   */
  public function translate_shouldCallTransMethodOnTranslator()
  {
    $this->translator->shouldReceive('trans')->with('messageToTrans', array(), 'messages', 'pl')->once();
    $this->feature->translate('messageToTrans');
  }

  /**
   * Do sth.
   *
   * @test
   */
  public function addFixture_someFixtureNamePassed_shouldAddFixtureToRegistry()
  {
    $this->feature->setParentContext($this->getParentContextMock());
    $this->feature->getMainContext()->getRegistry()->shouldReceive('add')->with(\Neducatio\TestBundle\Features\Context\BaseSubContext::FIXTURES_KEY, "somefixture")->once();
    $this->feature->addFixture("somefixture");
  }

  /**
   * Do sth.
   *
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Sub context has no parent
   */
  public function loadFixtures_contextWithoutParent_shouldThrowException()
  {
    $this->feature->kernel = null;
    $this->feature->loadFixtures(array());
  }

  /**
   * Do sth.
   *
   * @test
   */
  public function loadFixtures_contextWithParent_shouldCallLoadFixturesOnMainContext()
  {
    $parent = $this->getParentContextMock();
    $mainContext = $parent->getMainContext();
    $mainContext->shouldReceive('loadFixtures')->with(array())->once();
    $this->feature->setParentContext($parent);
    $this->feature->loadFixtures(array());
  }
  /**
   * Do sth.
   *
   * @test
   */
  public function loadFixtures_nullPassed_shouldGetFixturesFromRegistry()
  {
    $parent = $this->getParentContextMock();
    $mainContext = $parent->getMainContext();
    $mainContext->shouldReceive('loadFixtures')->with(array())->once();
    $mainContext->getRegistry()->shouldReceive('get')->with(\Neducatio\TestBundle\Features\Context\BaseSubContext::FIXTURES_KEY)->once();
    $this->feature->setParentContext($parent);
    $this->feature->loadFixtures(null);
  }


  /**
   * Do sth.
   *
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Sub context has no parent
   */
  public function getReference_contextWithoutParent_shouldThrowException()
  {
    $this->feature->getReference('ref');
  }

  /**
   * Kernel is set to null to restore test conditions from version for symfony 2.3
   *
   * @test
   */
  public function getReference_contextWithParent_shouldCallGetReferenceOnMainContext()
  {
    $this->feature->kernel = null;
    $parent = $this->getParentContextMock();
    $mainContext = $parent->getMainContext();
    $mainContext->shouldReceive('getReference')->with('ref')->once();
    $this->feature->setParentContext($parent);
    $this->feature->getReference('ref');
  }

  /**
   * Do sth.
   *
   * @test
   */
  public function getRegistry_shouldCallGetRegistryOnMainContext()
  {
    $parent = $this->getParentContextMock();
    $mainContext = $parent->getMainContext();
    $mainContext->shouldReceive('getRegistry')->once();
    $this->feature->setParentContext($parent);
    $this->feature->getRegistry();
  }

  /**
   * Do sth.
   *
   * @test
   * @expectedException RuntimeException
   * @expectedExceptionMessage Sub context has no parent
   */
  public function getRegistry_maintContextNotSet_shouldThrowException()
  {
    $this->feature->getRegistry();
  }

    /**
     * Do sth.
     *
     * @test
     * @group wip
     */
    public function setPageByName()
    {
        $this->feature->setParentContext($this->getParentContextMock());
        $pageObjectClass = 'somyPageObjectClass';
        $browserPage = m::mock('Behat\Mink\Element\TraversableElement');
        $this->feature->getMainContext()->getSession()->shouldReceive('getPage')->andReturn($browserPage);
        $expectedPageObject = m::mock('\Neducatio\TestBundle\PageObject\BasePageObject');
        $this->builder->shouldReceive('build')->with($pageObjectClass, $browserPage)->andReturn($expectedPageObject);

        $this->feature->getMainContext()->getRegistry()->shouldReceive('set')->once()->with(TestableBaseSubContext::PAGE_KEY, $expectedPageObject);

        $this->feature->setPageByName($pageObjectClass);
    }


  private function getKernelMock()
  {
    $container = m::mock('stdClass');
    $container->shouldReceive('get')->andReturn($this->translator);
    $kernel = m::mock('Symfony\Component\HttpKernel\KernelInterface');
    $kernel->shouldReceive('getContainer')->andReturn($container);

    return $kernel;
  }
}
