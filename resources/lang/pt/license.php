<?php

declare(strict_types=1);

return [
    'exceptions' => [
        'activation_limit_reached' => 'Limite de ativacoes atingido para esta licenca.',
        'domain_not_allowed' => 'Dominio :domain nao esta permitido para esta licenca.',
        'domain_blocked' => 'Dominio :domain esta bloqueado para esta licenca.',
        'version_not_covered' => 'Versao :version nao esta coberta por esta licenca.',
        'insufficient_credits' => 'Creditos insuficientes. Necessario: :required, Disponivel: :available',
        'license_not_found' => 'Licenca nao encontrada.',
        'license_not_loaded' => 'Licenca deve estar carregada antes de realizar esta operacao.',
        'license_expired' => 'Licenca expirou em :date.',
        'license_revoked' => 'Licenca foi revogada.',
        'license_suspended' => 'Licenca esta atualmente suspensa.',
        'usage_not_configured' => 'Rastreamento de uso nao esta configurado para esta licenca.',
        'usage_not_configured_for_credits' => 'Uso nao configurado para licenca de creditos.',
    ],

    'status' => [
        'active' => 'Ativa',
        'expired' => 'Expirada',
        'suspended' => 'Suspensa',
        'revoked' => 'Revogada',
    ],

    'type' => [
        'perpetual' => 'Perpetua',
        'subscription' => 'Assinatura',
        'trial' => 'Teste',
    ],

    'events' => [
        'activated' => 'Licenca ativada',
        'deactivated' => 'Licenca desativada',
        'suspended' => 'Licenca suspensa',
        'resumed' => 'Licenca retomada',
        'revoked' => 'Licenca revogada',
        'renewed' => 'Licenca renovada',
        'upgraded' => 'Licenca atualizada',
        'downgraded' => 'Licenca rebaixada',
    ],
];
