<?php

declare(strict_types=1);

namespace Metasyntactical\Composer\LicenseCheck\GrumPHP;

use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ComposerLicense task.
 */
class ComposerLicense extends AbstractExternalTask
{
  public static function getConfigurableOptions(): OptionsResolver
  {
    $resolver = new OptionsResolver();
    $resolver->setDefaults([
      'file' => './composer.json',
    ]);

    $resolver->addAllowedTypes('file', ['string']);

    return $resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function canRunInContext(ContextInterface $context): bool
  {
    return $context instanceof GitPreCommitContext || $context instanceof RunContext;
  }

  /**
   * {@inheritdoc}
   */
  public function run(ContextInterface $context): TaskResultInterface
  {
    $config = $this->getConfig()->getOptions();

    $arguments = $this->processBuilder->createArgumentsForCommand('composer');
    $arguments->add('check-licenses');


    $process = $this->processBuilder->buildProcess($arguments);
    $process->run();

    if (!$process->isSuccessful()) {
      return TaskResult::createFailed($this, $context, $this->formatter->format($process));
    }

    return TaskResult::createPassed($this, $context);
  }
}
