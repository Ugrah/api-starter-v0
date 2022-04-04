<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220213143225 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs

        //  Table structure for table `user`
        $this->addSql('CREATE TABLE `user` (`id` int(11) NOT NULL AUTO_INCREMENT, `email` varchar(180) COLLATE utf8_unicode_ci NOT NULL, `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL, `lastname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL, `roles` json NOT NULL, `username` varchar(100) COLLATE utf8_unicode_ci NOT NULL, `firstname` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL, `phonenumber` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL, `status` tinyint(1) DEFAULT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci');

        $this->addSql('CREATE TABLE `refresh_tokens` (`id` int(11) NOT NULL AUTO_INCREMENT, `refresh_token` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL, `username` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL, `valid` datetime NOT NULL, PRIMARY KEY (id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

        $this->addSql("INSERT INTO `user` (`id`, `email`, `password`, `lastname`, `roles`, `username`, `firstname`, `phonenumber`, `status`) VALUES ('1', 'admin@maxmind.ma', '$2y$13\$xad1ulk.Vt70njoH64AJxuWgrxwQu4jXFX280DnCwJIC9GHzj1Q4K', 'Maxmind', '[\"ROLE_ADMIN\", \"ROLE_USER\"]', 'admin', 'Admin', NULL, '1')");
        $this->addSql("INSERT INTO `user` (`id`, `email`, `password`, `lastname`, `roles`, `username`, `firstname`, `phonenumber`, `status`) VALUES ('2', 'oneuser@test.com', '$2y$13\$KpaA4ynhm3zTrmrWzzVZ1uVmjxD8EF6O2oJxbAq4O7Wn9/TL9eCHq', 'Peace', '[\"ROLE_USER\"]', 'OnePeace', 'One', NULL, '1')
        ");
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        // $this->addSql('DROP TABLE user');

        // Indexes for table `user`
        $this->addSql('ALTER TABLE `user` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`);');
        $this->addSql('DROP TABLE user');
        $this->addSql('ALTER TABLE `refresh_tokens` ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `UNIQ_9BACE7E1C74F2195` (`refresh_token`)');
    }
}
