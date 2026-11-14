<?php
// Include the validation functions to be tested
require_once '../includes/validation.php';

// Helper function to render test results using the requested labels
function assertTest($testName, $expectedOutcome, $actualError) {
    // Determine if validation passed (expected empty error string) or failed (non-empty error string)
    $hasError = !empty($actualError);
    $passed = ($expectedOutcome === $hasError);
    
    $statusClass = $passed ? 'test-pass' : 'test-fail';
    $statusText = $passed ? 'PASS' : 'FAIL';
    $resultLabel = $hasError ? 'Fails validation' : 'Passes validation';
    
    echo "<div class='test-item {$statusClass}'>";
    echo "<strong>{$testName}</strong>: Expected [<strong>{$resultLabel}</strong>] — <span>{$statusText}</span>";
    echo "</div>";
}

// Include shared header and navigation templates

?>

<div class="container">
    <div class="top-action-bar">
        
    </div>

    <section>
        <h2 class="page-title">Validation Test Harness</h2>
        <p>Running verification against the required test cases:</p>

        <div class="test-results" style="margin-top: 20px;">
            <?php
            // 1. Valid Gift Card data -> Expected: Passes validation (empty error string)
            $validNameErr = validateCardName("Amazon Gift Card");
            $validTypeErr = validateCardType("Retail");
            $validValErr  = validateCardValue("50.00");
            $validPtsErr  = validatePoints("500");
            $isFullyValid = ($validNameErr === '' && $validTypeErr === '' && $validValErr === '' && $validPtsErr === '');
            assertTest("Valid Gift Card data", false, $isFullyValid ? '' : 'Error');

            // 2. Blank Card Name -> Expected: Fails validation
            $blankNameErr = validateCardName("");
            assertTest("Blank Card Name", true, $blankNameErr);

            // 3. Altered or invalid Card Type -> Expected: Fails validation
            $invalidTypeErr = validateCardType("InvalidType");
            assertTest("Altered or invalid Card Type", true, $invalidTypeErr);

            // 4. Zero or negative Card Value -> Expected: Fails validation
            $zeroValErr = validateCardValue("0");
            assertTest("Zero or negative Card Value", true, $zeroValErr);

            // 5. Invalid Points Required -> Expected: Fails validation
            $invalidPtsErr = validatePoints("-10");
            assertTest("Invalid Points Required", true, $invalidPtsErr);
            ?>
        </div>
    </section>
</div>

