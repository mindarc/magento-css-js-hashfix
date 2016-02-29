<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class MindArc_Core_Model_Design_Package extends Mage_Core_Model_Design_Package
{
    /**
     * Merge specified javascript files and return URL to the merged file on success
     *
     * @param $files
     * @return string
     */
    public function getMergedJsUrl($files)
    {
        // MindArc Fix - This function is created to create the hashed filename with host and port retrieved using
        // Magento standard functions, this returns back a md5 hash to be used with creating and moving the file to the directory.
        $filename = $this->getFilenameHashed($files);

        $targetFilename = $filename . '.js';
        $targetDir = $this->_initMergerDir('js');
        if (!$targetDir) {
            return '';
        }
        if ($this->_mergeFiles($files, $targetDir . DS . $targetFilename, false, null, 'js')) {
            return Mage::getBaseUrl('media', Mage::app()->getRequest()->isSecure()) . 'js/' . $targetFilename;
        }
        return '';
    }

    /**
     * Merge specified css files and return URL to the merged file on success
     *
     * @param $files
     * @return string
     */
    public function getMergedCssUrl($files)
    {
        // MindArc Fix - used to determine which directory to push the css file to, whether its css_secure or css for http/https
        $isSecure = Mage::app()->getRequest()->isSecure();
        $mindarc_coreDir = $isSecure ? 'css_secure' : 'css';

        $targetDir = $this->_initMergerDir($mindarc_coreDir);
        if (!$targetDir) {
            return '';
        }

        // base hostname & port
        $baseMediaUrl = Mage::getBaseUrl('media', $isSecure);

        // MindArc Fix - This function is created to create the hashed filename with host and port retrieved using
        // Magento standard functions, this returns back a md5 hash to be used with creating and moving the file to the directory.
        $filename = $this->getFilenameHashed($files);

        // merge into target file
        $targetFilename = $filename . '.css';
        $mergeFilesResult = $this->_mergeFiles(
            $files, $targetDir . DS . $targetFilename,
            false,
            array($this, 'beforeMergeCss'),
            'css'
        );

        if ($mergeFilesResult) {
            return $baseMediaUrl . $mindarc_coreDir . '/' . $targetFilename;
        }
        return '';
    }

    /**
     * Returns a hash based on the files array passed in with the hostname and port determined by Magento core functions
     *
     * @param $files
     * @return string
     */
    public function getFilenameHashed($files)
    {
        $hashed_str    = '';
        $port       = 80;
        $hostname   = Mage::getBaseUrl();

        if (Mage::app()->getRequest()->isSecure()){
            $port = 443;
        }

        foreach ($files as $file) {
            $hashed_str .= filemtime($file) .',';
        }

        $hashed_str = md5($hashed_str . "|{$hostname}|{$port}");

        return $hashed_str;
    }

}
