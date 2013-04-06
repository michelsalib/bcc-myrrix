<?php

namespace BCC\Myrrix;

use Guzzle\Parser\UriTemplate\UriTemplateInterface;

/**
 * Hack the original UriTemplate class to provide adapted parsing for the Myrrix uris
 */
class MyrrixUriTemplate implements UriTemplateInterface
{
    /**
     * @var UriTemplateInterface
     */
    protected $uriTemplate;

    function __construct(UriTemplateInterface $uriTemplate)
    {
        $this->uriTemplate = $uriTemplate;
    }

    public function expand($template, array $variables)
    {
        if ($template == '/recommendToAnonymous{/preferences*}') {
            $result = '/recommendToAnonymous';
            foreach ($variables['preferences'] as $key => $variable) {
                $result .= sprintf('/%d=%f', $key, $variable);
            }
            return $result;
        }

        if ($template == '/estimateForAnonymous/{itemID}{/preferences*}') {
            $result = '/estimateForAnonymous/'.$variables['itemID'];
            foreach ($variables['preferences'] as $key => $variable) {
                $result .= sprintf('/%d=%f', $key, $variable);
            }
            return $result;
        }

        return $this->uriTemplate->expand($template, $variables);
    }
}
