# white-label

![Docker](https://img.shields.io/badge/docker-%230db7ed.svg?style=for-the-badge&logo=docker&logoColor=white)
![GitHub Actions](https://img.shields.io/badge/github%20actions-%232671E5.svg?style=for-the-badge&logo=githubactions&logoColor=white)
![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![Symfony](https://img.shields.io/badge/symfony-%23000000.svg?style=for-the-badge&logo=symfony&logoColor=white)
![HTML](https://img.shields.io/badge/HTML5-E34F26?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?style=for-the-badge&logo=css3&logoColor=white)
![Git](https://img.shields.io/badge/Git-F05032?style=for-the-badge&logo=git&logoColor=white)
![VSCode](https://img.shields.io/badge/Visual_Studio-0078d7?style=for-the-badge&logo=visual%20studio&logoColor=white)

A white label product for all applications

### Local/Codespace Installtion & SetUp
* How to run the program
```
Install Visual Studio extensions like docker
Run docker compose up
```

### Docker Commands
* cd docker/environments/local -running this command will set-up all the services
```
docker compose up
```

* If you update any docker files you may be required to run this
```
docker compose down
docker system prune -a (emoves images, containers, volumes, and networks)
docker compose up
```
### future enhancements
```
ability to work with any currency
In hindsight I should have probaly used an array value to store the different coins and the amount of coins required as this should make it easier to add more denomination coins
```

### Useful Composer Commands
Run In Application Directory

| Command | Description |
| ------ | ------ |
| ``` ./vendor/bin/phpcs src --standard=PSR12 --report=full --report-width=120 --colors -p ```      | View Codesniffer Report And suggestions |
| ``` ./vendor/bin/phpcbf src --standard=PSR12 --report=full --report-width=120 --colors -p ```     | Auto CodeSniffer Fixer |
| ``` ./vendor/bin/phpstan analyse --configuration=phpstan.dist.neon ```                            | PHPStan Analyzer |
| ``` ./vendor/bin/phpunit ```                                                                      | PHPUnit |


