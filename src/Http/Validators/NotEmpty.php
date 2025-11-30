<?

namespace Mini\Framework\Http\Validators;

use Attribute;
use TypeError;

#[Attribute(Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY)]
class NotEmpty implements Validator
{

  public function __construct() {}

  public function isValid(mixed $value): bool
  {
    if (is_string($value)) $value = trim($value);
    return !empty($value);
  }

}
