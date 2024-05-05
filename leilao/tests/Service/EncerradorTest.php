<?php

namespace Alura\Leilao\Tests\Service;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class Encerradortest extends TestCase
{
  public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
  {
    $fiat147 = new Leilao(
      'Fiat 147 0Km',
      new DateTimeImmutable('8 days ago'),
    );

    $variant = new Leilao(
      'Variant 1972 0Km',
      new DateTimeImmutable('10 days ago'),
    );

    $leilaoDao = $this->createMock(LeilaoDao::class);
    $leilaoDao->method('recuperarNaoFinalizados')->willReturn([$fiat147, $variant]);
    $leilaoDao->expects($this->exactly(2))->method('atualiza');
    // $leilaoDao->method('recuperarFinalizados')->willReturn([$fiat147, $variant]);
    // $leilaoDao->salva($fiat147);
    // $leilaoDao->salva($variant);

    $encerrador = new Encerrador($leilaoDao);
    $encerrador->encerra();

    $leiloes = [$fiat147, $variant];
    // $leiloes = $leilaoDao->recuperarFinalizados();
    self::assertCount(2, $leiloes);
    self::assertTrue($leiloes[0]->estaFinalizado());
    self::assertTrue($leiloes[1]->estaFinalizado());
    // self::assertEquals('Fiat 147 0Km', $leiloes[0]->recuperarDescricao());
    // self::assertEquals('Variant 1972 0Km', $leiloes[1]->recuperarDescricao());
  }
}

class LeilaoDaoMock extends LeilaoDao
{
  private $leiloes = [];

  public function salva(Leilao $leilao): void
  {
    $this->leiloes[] = $leilao;
  }

  public function recuperarNaoFinalizados(): array
  {
    return array_filter($this->leiloes, function (Leilao $leilao) {
      return !$leilao->estaFinalizado();
    });
  }

  public function recuperarFinalizados(): array
  {
    return array_filter($this->leiloes, function (Leilao $leilao) {
      return $leilao->estaFinalizado();
    });
  }

  public function atualiza(Leilao $leilao)
  {
  }
}