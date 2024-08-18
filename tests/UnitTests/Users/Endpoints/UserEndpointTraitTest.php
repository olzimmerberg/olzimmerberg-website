<?php

declare(strict_types=1);

namespace Olz\Tests\UnitTests\Users\Endpoints;

use Olz\Api\OlzEntityEndpoint;
use Olz\Tests\UnitTests\Common\TraitConcreteEndpoint;
use Olz\Tests\UnitTests\Common\UnitTestCase;
use Olz\Users\Endpoints\UserEndpointTrait;
use PhpTypeScriptApi\Fields\FieldTypes;

class UserEndpointTraitConcreteEndpoint extends OlzEntityEndpoint {
    use UserEndpointTrait;
    use TraitConcreteEndpoint;
}

/**
 * @internal
 *
 * @covers \Olz\Users\Endpoints\UserEndpointTrait
 */
final class UserEndpointTraitTest extends UnitTestCase {
    public function testUserEndpointTrait(): void {
        $endpoint = new UserEndpointTraitConcreteEndpoint();
        $this->assertFalse($endpoint->usesExternalId());

        $field = $endpoint->getEntityDataField(/* allow_null= */ false);
        $this->assertTrue($field instanceof FieldTypes\ObjectField);
        $this->assertFalse($field->getAllowNull());
        $field_structure = $field->getFieldStructure();
        $keys = array_keys($field_structure);
        sort($keys);
        $this->assertSame([
            'avatarId',
            'birthdate',
            'city',
            'countryCode',
            'email',
            'firstName',
            'gender',
            'lastName',
            'password',
            'phone',
            'postalCode',
            'region',
            'siCardNumber',
            'solvNumber',
            'street',
            'username',
        ], $keys);
    }
}
