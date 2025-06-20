<?php
/**
 * MBlock v3.5 - Simplified Toggle Handler (no AJAX needed anymore)
 * Toggle state is now stored directly in form data via mblock_active field
 * @author MBlock v3.5 Extension
 * @package redaxo5
 * @license MIT
 */

class MBlockToggleHandler
{
    /**
     * Helper method to check if a block is active
     * @param array $block
     * @return bool
     */
    public static function isBlockActive($block)
    {
        if (!is_array($block)) {
            return true; // Default to active
        }
        
        // Check if block has mblock_active field and if it's active
        // Default to active if field doesn't exist (backwards compatibility)
        return !isset($block['mblock_active']) || $block['mblock_active'] == '1' || $block['mblock_active'] === true;
    }
    
    /**
     * Filter blocks to return only active ones
     * @param array $blocks
     * @return array
     */
    public static function filterActiveBlocks($blocks)
    {
        if (!is_array($blocks)) {
            return array();
        }
        
        $activeBlocks = array();
        foreach ($blocks as $index => $block) {
            if (self::isBlockActive($block)) {
                // Remove the mblock_active field from output to keep it clean
                $cleanBlock = is_array($block) ? $block : array();
                unset($cleanBlock['mblock_active']);
                $activeBlocks[] = $cleanBlock;
            }
        }
        
        return $activeBlocks;
    }
    
    /**
     * Get toggle state for debugging purposes
     * @param array $block
     * @return string
     */
    public static function getToggleState($block)
    {
        return self::isBlockActive($block) ? 'active' : 'inactive';
    }
    
    /**
     * Debug helper - log toggle information
     * @param array $blocks
     * @param string $context
     */
    public static function debugToggleStates($blocks, $context = 'MBlock')
    {
        if (!rex::isDebugMode()) {
            return;
        }
        
        if (!is_array($blocks)) {
            return;
        }
        
        $debugInfo = array();
        foreach ($blocks as $index => $block) {
            $debugInfo[] = array(
                'index' => $index,
                'active' => self::isBlockActive($block),
                'has_toggle_field' => is_array($block) && isset($block['mblock_active']),
                'toggle_value' => is_array($block) && isset($block['mblock_active']) ? $block['mblock_active'] : 'not set'
            );
        }
        
        dump($context . ' v3.5 Toggle States:', $debugInfo);
    }
}
