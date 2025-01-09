<?php
/**
 * Sensebank Payment Helper
 *
 * @copyright Copyright (c) 2019 Sensebank Bank
 */
namespace SENSEPAYMENT\Sensebank\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\ObjectManagerInterface;
class Data extends AbstractHelper
{
    const XML_PATH = 'payment/sensebank/';
    /**
     * @var \Magento\Framework\Locale\ResolverInterface $localeResolver
     */
    protected $_localeResolver;
    /**
     * Magento 2.2.0 uses SerializerInterface to serialize data
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $_serializer;
    /**
     * Data constructor.
     * @param Context $context
     */
    public function __construct(
        Context                  $context,
        ResolverInterface        $localeResolver,
        ProductMetadataInterface $productMetadata,
        ObjectManagerInterface   $objectManager
    )
    {
        parent::__construct($context);
        if (version_compare($productMetadata->getVersion(), '2.2.0', '>=')) {
            $this->_serializer = $objectManager->get('\Magento\Framework\Serialize\SerializerInterface');
        }
        $this->_localeResolver = $localeResolver;
    }
    /**
     * @param string $code
     * @param integer $storeId
     * @return mixed
     */
    public function getStoreConfig($code, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH . $code, ScopeInterface::SCOPE_STORE, $storeId
        );
    }
    /**
     * @param string $code
     * @param integer $storeId
     * @return bool
     */
    public function getStoreConfigFlag($code, $storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH . $code, ScopeInterface::SCOPE_STORE, $storeId
        );
    }
    public function getLanguage()
    {
        return $this->_localeResolver->getLocale();
    }
    public function serialize($data)
    {
        if (!empty($this->_serializer)) {
            return $this->_serializer->serialize($data);
        } else {
            return serialize($data);
        }
    }
    public function unserialize($data)
    {
        if (!empty($this->_serializer)) {
            return $this->_serializer->unserialize($data);
        } else {
            return unserialize($data);
        }
    }
    public function _sendGatewayData($data, $action_address, $headers = array(), $ca_info = null)
    {
        $curl_opt = array(
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_VERBOSE => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_URL => $action_address,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $data,
            CURLOPT_HEADER => true,
        );
        $ssl_verify_peer = false;
        if ($ca_info != null) {
            $ssl_verify_peer = true;
            $curl_opt[CURLOPT_CAINFO] = $ca_info;
        }
        $curl_opt[CURLOPT_SSL_VERIFYPEER] = $ssl_verify_peer;
        $ch = curl_init();
        curl_setopt_array($ch, $curl_opt);
        $response = curl_exec($ch);
        if ($response === false) {
            die (">>>> " . curl_error($ch));
        }
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);
        return substr($response, $header_size);
    }
}
