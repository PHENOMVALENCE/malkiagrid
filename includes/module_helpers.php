<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

function mgrid_table_exists(PDO $pdo, string $table): bool
{
    $stmt = $pdo->prepare('SHOW TABLES LIKE :table');
    $stmt->execute(['table' => $table]);
    return (bool) $stmt->fetchColumn();
}

function mscore_current_for_user(int $userId): ?array
{
    $pdo = db();
    if (!mgrid_table_exists($pdo, 'mscore_current_scores')) {
        return null;
    }
    $stmt = $pdo->prepare('SELECT * FROM mscore_current_scores WHERE user_id = :uid LIMIT 1');
    $stmt->execute(['uid' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!is_array($row)) {
        return null;
    }

    $breakdown = [
        ['category_name' => 'Profile', 'points_awarded' => (float) ($row['profile_score'] ?? 0), 'max_points' => 30, 'percentage' => ((float) ($row['profile_score'] ?? 0) / 30) * 100],
        ['category_name' => 'Documents', 'points_awarded' => (float) ($row['document_score'] ?? 0), 'max_points' => 30, 'percentage' => ((float) ($row['document_score'] ?? 0) / 30) * 100],
        ['category_name' => 'Banking', 'points_awarded' => (float) ($row['banking_score'] ?? 0), 'max_points' => 15, 'percentage' => ((float) ($row['banking_score'] ?? 0) / 15) * 100],
        ['category_name' => 'Training', 'points_awarded' => (float) ($row['training_score'] ?? 0), 'max_points' => 15, 'percentage' => ((float) ($row['training_score'] ?? 0) / 15) * 100],
        ['category_name' => 'Compliance', 'points_awarded' => (float) ($row['compliance_score'] ?? 0), 'max_points' => 10, 'percentage' => ((float) ($row['compliance_score'] ?? 0) / 10) * 100],
    ];

    $row['breakdown'] = $breakdown;
    $row['recommendations'] = [];
    if ((int) ($row['document_score'] ?? 0) < 20) {
        $row['recommendations'][] = 'Pakia na uhakikishe nyaraka zako zote muhimu.';
    }
    if ((int) ($row['profile_score'] ?? 0) < 20) {
        $row['recommendations'][] = 'Kamilisha sehemu za wasifu wa biashara na mawasiliano.';
    }
    if ($row['recommendations'] === []) {
        $row['recommendations'][] = 'Endelea kudumisha taarifa sahihi na ushiriki kwenye mafunzo.';
    }
    $row['tier_label'] = (string) ($row['tier'] ?? 'Beginner');
    $row['readiness_label'] = mscore_readiness_label((float) ($row['total_score'] ?? 0));
    return $row;
}

function calculateUserMScore(int $userId): array
{
    $pdo = db();
    $profileStmt = $pdo->prepare('SELECT profile_completion FROM user_profiles WHERE user_id = :uid LIMIT 1');
    $profileStmt->execute(['uid' => $userId]);
    $profileCompletion = (int) ($profileStmt->fetchColumn() ?: 0);
    $profileScore = (int) round(min(100, max(0, $profileCompletion)) * 0.30);

    $docsStmt = $pdo->prepare("SELECT COUNT(*) FROM user_documents WHERE user_id = :uid AND status = 'verified'");
    $docsStmt->execute(['uid' => $userId]);
    $verifiedDocs = (int) $docsStmt->fetchColumn();
    $documentScore = min(30, $verifiedDocs * 8);

    $trainingStmt = $pdo->prepare("SELECT COUNT(*) FROM training_registrations WHERE user_id = :uid AND participation_status IN ('attended','completed')");
    $trainingStmt->execute(['uid' => $userId]);
    $trainingCount = (int) $trainingStmt->fetchColumn();
    $trainingScore = min(15, $trainingCount * 5);

    $complianceScore = $verifiedDocs > 0 ? 10 : 0;
    $bankingScore = 5;
    $total = min(100, $profileScore + $documentScore + $trainingScore + $complianceScore + $bankingScore);
    $tier = mscore_tier_for_score((float) $total);

    $sql = 'INSERT INTO mscore_current_scores (user_id, total_score, profile_score, document_score, banking_score, training_score, compliance_score, tier, calculated_at)
            VALUES (:uid, :total, :profile, :docs, :bank, :training, :compliance, :tier, NOW())
            ON DUPLICATE KEY UPDATE total_score=VALUES(total_score), profile_score=VALUES(profile_score), document_score=VALUES(document_score),
            banking_score=VALUES(banking_score), training_score=VALUES(training_score), compliance_score=VALUES(compliance_score), tier=VALUES(tier), calculated_at=NOW()';
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'uid' => $userId, 'total' => $total, 'profile' => $profileScore, 'docs' => $documentScore,
        'bank' => $bankingScore, 'training' => $trainingScore, 'compliance' => $complianceScore, 'tier' => $tier,
    ]);

    return mscore_current_for_user($userId) ?? ['total_score' => $total, 'tier' => $tier];
}

function mscore_tier_for_score(float $score): string
{
    return match (true) {
        $score >= 80 => 'Gold',
        $score >= 60 => 'Silver',
        $score >= 40 => 'Bronze',
        default => 'Beginner',
    };
}

function mscore_tier_badge_class(string $tier): string
{
    return match (strtolower($tier)) {
        'gold' => 'warning',
        'silver' => 'secondary',
        'bronze' => 'dark',
        default => 'info',
    };
}

function mscore_readiness_label(float $score): string
{
    return $score >= 60 ? 'Uko tayari kwa fursa nyingi' : 'Boresha wasifu na nyaraka kuongeza score';
}

function opportunities_module_ready(PDO $pdo): bool
{
    return mgrid_table_exists($pdo, 'opportunities') && mgrid_table_exists($pdo, 'opportunity_applications');
}

function opportunities_list_for_public(PDO $pdo, ?string $type, ?int $catId, string $df, string $dt, ?string $list, ?string $q): array
{
    $sql = 'SELECT o.id, o.title, o.description, o.provider_name, o.location, o.eligibility, o.minimum_mscore, o.deadline, o.status,
                   "general" AS opportunity_type, "unspecified" AS format,
                   COALESCE(c.name_sw, c.name_en, "General") AS category_name
            FROM opportunities o
            LEFT JOIN opportunity_categories c ON c.id = o.category_id
            WHERE o.status = "published"';
    $params = [];
    if ($catId !== null) {
        $sql .= ' AND o.category_id = :cat';
        $params['cat'] = $catId;
    }
    if ($q !== null) {
        $sql .= ' AND (o.title LIKE :q OR o.description LIKE :q OR o.provider_name LIKE :q)';
        $params['q'] = '%' . $q . '%';
    }
    if ($df !== '') {
        $sql .= ' AND o.deadline >= :df';
        $params['df'] = $df;
    }
    if ($dt !== '') {
        $sql .= ' AND o.deadline <= :dt';
        $params['dt'] = $dt;
    }
    $sql .= ' ORDER BY o.deadline ASC, o.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function opportunities_get_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT o.*, "general" AS opportunity_type, "unspecified" AS format,
                                  COALESCE(c.name_sw, c.name_en, "General") AS category_name
                           FROM opportunities o LEFT JOIN opportunity_categories c ON c.id = o.category_id
                           WHERE o.id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}

function ot_opportunity_listing_state(array $o): string
{
    if (($o['status'] ?? '') !== 'published') {
        return 'closed';
    }
    $deadline = (string) ($o['deadline'] ?? '');
    if ($deadline !== '' && strtotime($deadline) < strtotime(date('Y-m-d'))) {
        return 'expired';
    }
    return 'active';
}

function opportunities_user_has_active_application(PDO $pdo, int $uid, int $opportunityId): bool
{
    $stmt = $pdo->prepare("SELECT id FROM opportunity_applications WHERE user_id = :uid AND opportunity_id = :oid AND status IN ('submitted','under_review','shortlisted','accepted') LIMIT 1");
    $stmt->execute(['uid' => $uid, 'oid' => $opportunityId]);
    return (bool) $stmt->fetchColumn();
}

function trainings_module_ready(PDO $pdo): bool
{
    return mgrid_table_exists($pdo, 'training_programs') && mgrid_table_exists($pdo, 'training_registrations');
}

function trainings_list_for_public(PDO $pdo, ?string $type, ?string $format, ?string $list, ?string $q): array
{
    $sql = 'SELECT id, title, description, provider AS provider_name, delivery_mode AS format, start_date AS schedule_start, end_date AS schedule_end,
                   "general" AS training_type, location, status
            FROM training_programs WHERE status = "published"';
    $params = [];
    if ($format !== null && $format !== '') {
        $sql .= ' AND delivery_mode = :fmt';
        $params['fmt'] = $format;
    }
    if ($q !== null) {
        $sql .= ' AND (title LIKE :q OR description LIKE :q OR provider LIKE :q)';
        $params['q'] = '%' . $q . '%';
    }
    $sql .= ' ORDER BY start_date ASC, created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function trainings_get_by_id(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT id, title, description, provider AS provider_name, delivery_mode AS format, start_date AS schedule_start, end_date AS schedule_end,
                                  "general" AS training_type, location, status, minimum_mscore, NULL AS trainer_name, NULL AS duration_label, eligibility
                           FROM training_programs WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}

function ot_training_listing_state(array $p): string
{
    if (($p['status'] ?? '') !== 'published') {
        return 'closed';
    }
    $start = (string) ($p['schedule_start'] ?? '');
    $end = (string) ($p['schedule_end'] ?? '');
    $today = date('Y-m-d');
    if ($end !== '' && $end < $today) {
        return 'past';
    }
    if ($start !== '' && $start > $today) {
        return 'active';
    }
    return 'active';
}

function trainings_user_has_active_registration(PDO $pdo, int $uid, int $trainingId): bool
{
    $stmt = $pdo->prepare("SELECT id FROM training_registrations WHERE user_id = :uid AND training_id = :tid AND registration_status IN ('pending','approved','waitlisted') LIMIT 1");
    $stmt->execute(['uid' => $uid, 'tid' => $trainingId]);
    return (bool) $stmt->fetchColumn();
}

function mbenefits_module_ready(PDO $pdo): bool
{
    return mgrid_table_exists($pdo, 'benefit_offers') && mgrid_table_exists($pdo, 'benefit_claims');
}

function mbenefits_list_active_offers(PDO $pdo, ?int $catId): array
{
    $sql = 'SELECT o.*, COALESCE(c.name_sw, c.name_en, "General") AS category_name, p.name AS provider_name,
                   o.description AS short_description, o.description AS full_description,
                   CONCAT("Min M-SCORE ", o.minimum_mscore) AS value_label,
                   o.start_date AS valid_from, o.end_date AS valid_to,
                   "offer" AS benefit_type
            FROM benefit_offers o
            LEFT JOIN benefit_categories c ON c.id = o.category_id
            LEFT JOIN benefit_providers p ON p.id = o.provider_id
            WHERE o.status = "published"';
    $params = [];
    if ($catId !== null) {
        $sql .= ' AND o.category_id = :cat';
        $params['cat'] = $catId;
    }
    $sql .= ' ORDER BY o.created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}

function mbenefits_get_offer(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare('SELECT o.*, COALESCE(c.name_sw, c.name_en, "General") AS category_name, p.name AS provider_name,
                                  o.description AS short_description, o.description AS full_description,
                                  o.eligibility AS terms_and_conditions, o.eligibility AS redemption_method,
                                  CONCAT("Min M-SCORE ", o.minimum_mscore) AS value_label,
                                  o.start_date AS valid_from, o.end_date AS valid_to,
                                  "offer" AS benefit_type
                           FROM benefit_offers o
                           LEFT JOIN benefit_categories c ON c.id = o.category_id
                           LEFT JOIN benefit_providers p ON p.id = o.provider_id
                           WHERE o.id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return is_array($row) ? $row : null;
}

function mbenefits_evaluate_eligibility(PDO $pdo, int $uid, array $offer): array
{
    $score = 0;
    $stmt = $pdo->prepare('SELECT total_score FROM mscore_current_scores WHERE user_id = :uid LIMIT 1');
    $stmt->execute(['uid' => $uid]);
    $score = (int) ($stmt->fetchColumn() ?: 0);
    $ok = $score >= (int) ($offer['minimum_mscore'] ?? 0);
    return ['ok' => $ok, 'score' => $score];
}

function mbenefits_get_eligibility_message(PDO $pdo, int $uid, int $offerId): string
{
    $offer = mbenefits_get_offer($pdo, $offerId);
    if (!$offer) {
        return 'Ofa haijapatikana.';
    }
    $ev = mbenefits_evaluate_eligibility($pdo, $uid, $offer);
    if ($ev['ok']) {
        return 'Unakidhi vigezo vya kuomba ofa hii.';
    }
    return 'Boresha M-SCORE yako ili ufikie kiwango kinachohitajika.';
}

function mbenefits_eligibility_rule_summary(array $offer): string
{
    return 'M-SCORE angalau ' . (int) ($offer['minimum_mscore'] ?? 0);
}

function mbenefits_benefit_type_badge(string $type): string { return 'primary'; }
function mbenefits_benefit_type_label(string $type): string { return 'Offer'; }

function mbenefits_claim_status_badge(string $status): string
{
    return match ($status) {
        'approved', 'redeemed' => 'success',
        'rejected', 'cancelled' => 'danger',
        default => 'warning',
    };
}

function mbenefits_claim_status_label(string $status): string
{
    return match ($status) {
        'pending' => 'Inasubiri',
        'approved' => 'Imeidhinishwa',
        'rejected' => 'Imekataliwa',
        'redeemed' => 'Imetumika',
        'cancelled' => 'Imefutwa',
        default => $status,
    };
}

function mfund_setting(PDO $pdo, string $key, string $default): string
{
    $stmt = $pdo->prepare('SELECT setting_value FROM platform_settings WHERE setting_key = :k LIMIT 1');
    $stmt->execute(['k' => $key]);
    $val = $stmt->fetchColumn();
    return $val !== false && $val !== null && $val !== '' ? (string) $val : $default;
}

function checkFundingEligibility(int $uid): array
{
    $pdo = db();
    $scoreStmt = $pdo->prepare('SELECT total_score FROM mscore_current_scores WHERE user_id = :uid LIMIT 1');
    $scoreStmt->execute(['uid' => $uid]);
    $score = (int) ($scoreStmt->fetchColumn() ?: 0);

    $profileStmt = $pdo->prepare('SELECT profile_completion FROM user_profiles WHERE user_id = :uid LIMIT 1');
    $profileStmt->execute(['uid' => $uid]);
    $profile = (int) ($profileStmt->fetchColumn() ?: 0);

    $nidaStmt = $pdo->prepare("SELECT id FROM user_documents d INNER JOIN document_types t ON t.id = d.document_type_id WHERE d.user_id = :uid AND t.code='nida' AND d.status='verified' LIMIT 1");
    $nidaStmt->execute(['uid' => $uid]);
    $hasNida = (bool) $nidaStmt->fetchColumn();

    $minScore = (int) mfund_setting($pdo, 'minimum_mscore_for_funding', '60');
    $minProfile = (int) mfund_setting($pdo, 'minimum_profile_completion_for_funding', '70');
    $checks = [
        ['ok' => $score >= $minScore, 'message' => "M-SCORE >= {$minScore}"],
        ['ok' => $profile >= $minProfile, 'message' => "Ukamilifu wa wasifu >= {$minProfile}%"],
        ['ok' => $hasNida, 'message' => 'NIDA imethibitishwa'],
    ];
    $eligible = $checks[0]['ok'] && $checks[1]['ok'] && $checks[2]['ok'];
    return ['eligible' => $eligible, 'score' => $score, 'min_score' => $minScore, 'checks' => $checks];
}

function mfund_status_badge(string $status): string
{
    return match ($status) {
        'approved', 'disbursed', 'completed' => 'success',
        'rejected', 'cancelled', 'defaulted' => 'danger',
        'under_review', 'more_info_requested', 'active_repayment' => 'warning',
        default => 'secondary',
    };
}

function mfund_status_label(string $status): string
{
    return str_replace('_', ' ', ucfirst($status));
}

function fundingRepaymentTotals(PDO $pdo, int $appId): array
{
    $sumDue = $pdo->prepare('SELECT COALESCE(SUM(amount_due),0) FROM funding_repayment_schedules WHERE application_id = :id');
    $sumDue->execute(['id' => $appId]);
    $expected = (float) $sumDue->fetchColumn();

    $sumPaid = $pdo->prepare('SELECT COALESCE(SUM(amount_paid),0) FROM funding_repayment_logs WHERE application_id = :id');
    $sumPaid->execute(['id' => $appId]);
    $paid = (float) $sumPaid->fetchColumn();

    $overdue = $pdo->prepare("SELECT COUNT(*) FROM funding_repayment_schedules WHERE application_id = :id AND status = 'late'");
    $overdue->execute(['id' => $appId]);
    $overdueCount = (int) $overdue->fetchColumn();

    return ['expected_total' => $expected, 'paid_total' => $paid, 'balance' => max(0, $expected - $paid), 'overdue_count' => $overdueCount];
}

function opportunity_application_status_badge(string $status): string { return mfund_status_badge($status); }
function opportunity_application_status_label(string $status): string { return mfund_status_label($status); }
function opportunity_completion_status_label(string $status): string { return $status !== '' ? mfund_status_label($status) : '—'; }
function opportunity_certificate_status_label(string $status): string { return $status !== '' ? mfund_status_label($status) : '—'; }

function training_registration_status_badge(string $status): string { return mfund_status_badge($status); }
function training_registration_status_label(string $status): string { return mfund_status_label($status); }
function training_participation_status_badge(string $status): string { return mfund_status_badge($status); }
function training_participation_status_label(string $status): string { return mfund_status_label($status); }
function training_certificate_status_badge(string $status): string { return mfund_status_badge($status); }
function training_certificate_status_label(string $status): string { return mfund_status_label($status); }
