<?php

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.
// Returns the public 'validator' shared service.

return $this->services['validator'] = ${($_ = isset($this->services['validator.builder']) ? $this->services['validator.builder'] : $this->load(__DIR__.'/getValidator_BuilderService.php')) && false ?: '_'}->getValidator();