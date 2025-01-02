<?php

namespace Olz\Entity\Users;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\ORM\Mapping as ORM;
use Olz\Entity\Common\DataStorageInterface;
use Olz\Entity\Common\DataStorageTrait;
use Olz\Entity\Common\OlzEntity;
use Olz\Entity\Common\SearchableInterface;
use Olz\Entity\Roles\Role;
use Olz\Repository\Users\UserRepository;

#[ORM\Table(name: 'users')]
#[ORM\Index(name: 'username_index', columns: ['username'])]
#[ORM\Entity(repositoryClass: UserRepository::class)]
class User extends OlzEntity implements DataStorageInterface, SearchableInterface {
    use DataStorageTrait;

    #[ORM\Id]
    #[ORM\Column(type: 'integer', nullable: false)]
    #[ORM\GeneratedValue]
    public int $id;

    #[ORM\Column(type: 'integer', nullable: true)]
    public ?int $parent_user;

    #[ORM\Column(type: 'string', nullable: false)]
    public string $username;

    #[ORM\Column(type: 'string', nullable: true)]
    public ?string $old_username;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $password;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $email;

    #[ORM\Column(type: 'boolean', nullable: false)]
    public bool $email_is_verified;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $email_verification_token;

    #[ORM\Column(type: 'text', nullable: false)]
    public string $first_name;

    #[ORM\Column(type: 'text', nullable: false)]
    public string $last_name;

    #[ORM\Column(type: 'string', length: 2, nullable: true, options: ['comment' => 'M(ale), F(emale), or O(ther)'])]
    public ?string $gender;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $street;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $postal_code;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $city;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $region;

    #[ORM\Column(type: 'string', length: 3, nullable: true, options: ['comment' => 'two-letter code (ISO-3166-alpha-2)'])]
    public ?string $country_code;

    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTime $birthdate;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $phone;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $avatar_image_id;

    #[ORM\Column(type: 'text', nullable: false)]
    public string $permissions;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $root;

    #[ORM\Column(type: 'string', length: 3, nullable: true, options: ['comment' => 'Aktiv, Ehrenmitglied, Verein, Sponsor'])]
    public ?string $member_type;

    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTime $member_last_paid;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => 0])]
    public bool $wants_postal_mail;

    #[ORM\Column(type: 'text', nullable: true, options: ['comment' => 'if not {m: Herr, f: Frau, o: }'])]
    public ?string $postal_title;

    #[ORM\Column(type: 'text', nullable: true, options: ['comment' => "if not 'First Last'"])]
    public ?string $postal_name;

    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTime $joined_on;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $joined_reason;

    #[ORM\Column(type: 'date', nullable: true)]
    public ?\DateTime $left_on;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $left_reason;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $solv_number;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $si_card_number;

    #[ORM\Column(type: 'text', nullable: false, options: ['default' => ''])]
    public string $notes;

    /** @var Collection<int|string, Role>&iterable<Role> */
    #[ORM\JoinTable(name: 'users_roles')]
    #[ORM\ManyToMany(targetEntity: Role::class, inversedBy: 'users')]
    private Collection $roles;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTime $last_login_at;

    public function __construct() {
        $this->roles = new ArrayCollection();
    }

    public function getId(): ?int {
        return $this->id ?? null;
    }

    public function setId(int $new_id): void {
        $this->id = $new_id;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function setUsername(string $new_username): void {
        $this->username = $new_username;
    }

    public function getOldUsername(): ?string {
        return $this->old_username;
    }

    public function setOldUsername(?string $new_old_username): void {
        $this->old_username = $new_old_username;
    }

    public function getPasswordHash(): ?string {
        return $this->password;
    }

    public function setPasswordHash(?string $new_password_hash): void {
        $this->password = $new_password_hash;
    }

    public function getEmail(): ?string {
        return $this->email;
    }

    public function setEmail(?string $new_email): void {
        $this->email = $new_email;
    }

    public function isEmailVerified(): bool {
        return $this->email_is_verified;
    }

    public function setEmailIsVerified(bool $new_email_is_verified): void {
        $this->email_is_verified = $new_email_is_verified;
    }

    public function getEmailVerificationToken(): ?string {
        return $this->email_verification_token;
    }

    public function setEmailVerificationToken(?string $new_email_verification_token): void {
        $this->email_verification_token = $new_email_verification_token;
    }

    public function getFirstName(): string {
        return $this->first_name;
    }

    public function setFirstName(string $new_first_name): void {
        $this->first_name = $new_first_name;
    }

    public function getLastName(): string {
        return $this->last_name;
    }

    public function setLastName(string $new_last_name): void {
        $this->last_name = $new_last_name;
    }

    public function getFullName(): string {
        return "{$this->getFirstName()} {$this->getLastName()}";
    }

    public function getGender(): ?string {
        return $this->gender;
    }

    public function setGender(?string $new_gender): void {
        $this->gender = $new_gender;
    }

    public function getStreet(): ?string {
        return $this->street;
    }

    public function setStreet(?string $new_street): void {
        $this->street = $new_street;
    }

    public function getPostalCode(): ?string {
        return $this->postal_code;
    }

    public function setPostalCode(?string $new_postal_code): void {
        $this->postal_code = $new_postal_code;
    }

    public function getCity(): ?string {
        return $this->city;
    }

    public function setCity(?string $new_city): void {
        $this->city = $new_city;
    }

    public function getRegion(): ?string {
        return $this->region;
    }

    public function setRegion(?string $new_region): void {
        $this->region = $new_region;
    }

    public function getCountryCode(): ?string {
        return $this->country_code;
    }

    public function setCountryCode(?string $new_country_code): void {
        $this->country_code = $new_country_code;
    }

    public function getBirthdate(): ?\DateTime {
        return $this->birthdate;
    }

    public function setBirthdate(?\DateTime $new_birthdate): void {
        $this->birthdate = $new_birthdate;
    }

    public function getPhone(): ?string {
        return $this->phone;
    }

    public function setPhone(?string $new_phone): void {
        $this->phone = $new_phone;
    }

    public function getPermissions(): string {
        return $this->permissions;
    }

    public function setPermissions(string $new_permissions): void {
        $this->permissions = $new_permissions;
    }

    /** @return array<string, true> */
    public function getPermissionMap(): array {
        $permission_list = preg_split('/[ ]+/', $this->permissions ?? '');
        if (!is_array($permission_list)) {
            return [];
        }
        $permission_map = [];
        foreach ($permission_list as $permission) {
            if (strlen($permission) > 0) {
                $permission_map[$permission] = true;
            }
        }
        return $permission_map;
    }

    /** @param array<string, bool> $new_value */
    public function setPermissionMap(array $new_value): void {
        $permission_list = [];
        foreach ($new_value as $key => $value) {
            if ($value) {
                $permission_list[] = $key;
            }
        }
        $this->permissions = ' '.implode(' ', $permission_list).' ';
    }

    public function hasPermission(string $has_permission): bool {
        $permission_map = $this->getPermissionMap();
        return $permission_map[$has_permission] ?? false;
    }

    public function addPermission(string $add_permission): void {
        $permission_map = $this->getPermissionMap();
        $permission_map[$add_permission] = true;
        $this->setPermissionMap($permission_map);
    }

    public function removePermission(string $remove_permission): void {
        $permission_map = $this->getPermissionMap();
        $permission_map[$remove_permission] = false;
        $this->setPermissionMap($permission_map);
    }

    public function getRoot(): ?string {
        return $this->root;
    }

    public function setRoot(?string $new_root): void {
        $this->root = $new_root;
    }

    public function getParentUserId(): ?int {
        return $this->parent_user;
    }

    public function setParentUserId(?int $new_parent_user_id): void {
        $this->parent_user = $new_parent_user_id;
    }

    public function getMemberType(): ?string {
        return $this->member_type;
    }

    public function setMemberType(?string $new_member_type): void {
        $this->member_type = $new_member_type;
    }

    public function getMemberLastPaid(): ?\DateTime {
        return $this->member_last_paid;
    }

    public function setMemberLastPaid(?\DateTime $new_member_last_paid): void {
        $this->member_last_paid = $new_member_last_paid;
    }

    public function getWantsPostalMail(): bool {
        return $this->wants_postal_mail;
    }

    public function setWantsPostalMail(bool $new_wants_postal_mail): void {
        $this->wants_postal_mail = $new_wants_postal_mail;
    }

    public function getPostalTitle(): ?string {
        return $this->postal_title;
    }

    public function setPostalTitle(?string $new_postal_title): void {
        $this->postal_title = $new_postal_title;
    }

    public function getPostalName(): ?string {
        return $this->postal_name;
    }

    public function setPostalName(?string $new_postal_name): void {
        $this->postal_name = $new_postal_name;
    }

    public function getJoinedOn(): ?\DateTime {
        return $this->joined_on;
    }

    public function setJoinedOn(?\DateTime $new_joined_on): void {
        $this->joined_on = $new_joined_on;
    }

    public function getJoinedReason(): ?string {
        return $this->joined_reason;
    }

    public function setJoinedReason(?string $new_joined_reason): void {
        $this->joined_reason = $new_joined_reason;
    }

    public function getLeftOn(): ?\DateTime {
        return $this->left_on;
    }

    public function setLeftOn(?\DateTime $new_left_on): void {
        $this->left_on = $new_left_on;
    }

    public function getLeftReason(): ?string {
        return $this->left_reason;
    }

    public function setLeftReason(?string $new_left_reason): void {
        $this->left_reason = $new_left_reason;
    }

    public function getSolvNumber(): ?string {
        return $this->solv_number;
    }

    public function setSolvNumber(?string $new_solv_number): void {
        $this->solv_number = $new_solv_number;
    }

    public function getSiCardNumber(): ?string {
        return $this->si_card_number;
    }

    public function setSiCardNumber(?string $new_si_card_number): void {
        $this->si_card_number = $new_si_card_number;
    }

    public function getAvatarImageId(): ?string {
        return $this->avatar_image_id;
    }

    public function setAvatarImageId(?string $new_value): void {
        $this->avatar_image_id = $new_value;
    }

    public function getNotes(): string {
        return $this->notes;
    }

    public function setNotes(string $new_notes): void {
        $this->notes = $new_notes;
    }

    public function getLastLoginAt(): ?\DateTime {
        return $this->last_login_at;
    }

    public function setLastLoginAt(?\DateTime $new_last_login_at): void {
        $this->last_login_at = $new_last_login_at;
    }

    /** @return Collection<int|string, Role>&iterable<Role> */
    public function getRoles(): Collection {
        return $this->roles;
    }

    public function addRole(Role $role): void {
        if (!$this->roles->contains($role)) {
            $this->roles->add($role);
        }
    }

    public function removeRole(Role $role): void {
        $this->roles->removeElement($role);
    }

    // ---

    public function __toString(): string {
        $username = $this->getUsername();
        $id = $this->getId();
        return "{$username} (User ID: {$id})";
    }

    public function pretty(): string {
        $is_verified = $this->isEmailVerified() ? '✅ verified' : '❌ not verified';
        $has_permission = $this->hasPermission('verified_email') ? '✅ permission' : '❌ no permission';
        $roles = implode(', ', array_map(
            fn ($role): string => $role->getUsername(),
            [...$this->roles],
        ));
        $has_password = $this->getPasswordHash() ? '✅ password' : '❌ no password';
        $is_child = $this->getParentUserId() ? "🚸 child of {$this->getParentUserId()}" : '✅ parent';
        return <<<ZZZZZZZZZZ
            Name: {$this->getFullName()}
            Username: {$this->getUsername()} (old: {$this->getOldUsername()})
            E-Mail: {$this->getEmail()} ({$is_verified} / {$has_permission})
            Password: {$has_password} / {$is_child}
            Permissions: {$this->getPermissions()}
            Roles ({$this->roles->count()}): {$roles}
            ZZZZZZZZZZ;
    }

    public function softDelete(): void {
        $now_datetime = new \DateTime($this->dateUtils()->getIsoNow());
        $this->setOnOff(0);
        $this->setEmail('');
        $this->setPasswordHash('');
        $this->setPhone('');
        $this->setGender(null);
        $this->setBirthdate(null);
        $this->setStreet(null);
        $this->setPostalCode(null);
        $this->setCity(null);
        $this->setRegion(null);
        $this->setCountryCode(null);
        $this->setPermissions('');
        $this->setRoot(null);
        $this->setMemberType(null);
        $this->setMemberLastPaid(null);
        $this->setWantsPostalMail(false);
        $this->setPostalTitle(null);
        $this->setPostalName(null);
        $this->setJoinedOn(null);
        $this->setJoinedReason(null);
        $this->setLeftOn(null);
        $this->setLeftReason(null);
        $this->setSolvNumber(null);
        $this->setSiCardNumber(null);
        $this->setNotes('');
        $this->setLastModifiedAt($now_datetime);
        $this->roles->clear();
    }

    // ---

    public static function getIdFieldNameForSearch(): string {
        return 'id';
    }

    public function getIdForSearch(): int {
        return $this->getId() ?? 0;
    }

    public static function getCriteriaForQuery(string $query): Expression {
        return Criteria::expr()->orX(
            Criteria::expr()->contains('first_name', $query),
            Criteria::expr()->contains('last_name', $query),
            Criteria::expr()->contains('username', $query),
            Criteria::expr()->contains('email', $query),
        );
    }

    public function getTitleForSearch(): string {
        return $this->getFullName();
    }

    public static function getEntityNameForStorage(): string {
        return 'users';
    }

    public function getEntityIdForStorage(): string {
        return "{$this->getId()}";
    }
}
