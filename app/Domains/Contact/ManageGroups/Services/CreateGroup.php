<?php

namespace App\Domains\Contact\ManageGroups\Services;

use App\Interfaces\ServiceInterface;
use App\Models\Group;
use App\Services\BaseService;
use Illuminate\Support\Arr;

class CreateGroup extends BaseService implements ServiceInterface
{
    private array $data;

    /**
     * Get the validation rules that apply to the service.
     */
    public function rules(): array
    {
        return [
            'account_id' => 'required|uuid|exists:accounts,id',
            'vault_id' => 'required|uuid|exists:vaults,id',
            'author_id' => 'required|uuid|exists:users,id',
            'group_type_id' => 'nullable|integer|exists:group_types,id',
            'name' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get the permissions that apply to the user calling the service.
     */
    public function permissions(): array
    {
        return [
            'author_must_belong_to_account',
            'vault_must_belong_to_account',
            'author_must_be_vault_editor',
        ];
    }

    /**
     * Create a group.
     */
    public function execute(array $data): Group
    {
        $this->data = $data;
        $this->validate();

        $this->group = Group::create([
            'group_type_id' => Arr::get($data, 'group_type_id'),
            'vault_id' => $data['vault_id'],
            'name' => $this->valueOrNull($data, 'name'),
        ]);

        return $this->group;
    }

    private function validate(): void
    {
        $this->validateRules($this->data);

        if (($groupTypeId = Arr::get($this->data, 'group_type_id')) !== null) {
            $this->account()->groupTypes()
                ->findOrFail($groupTypeId);
        }
    }
}
