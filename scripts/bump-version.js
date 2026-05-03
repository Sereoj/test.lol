#!/usr/bin/env node

/**
 * Скрипт для автоматического обновления версии приложения
 * Обновляет версию в package.json и composer.json синхронно
 *
 * Использование:
 * npm run version:patch  - увеличить патч версию (1.0.0 -> 1.0.1)
 * npm run version:minor  - увеличить минорную версию (1.0.0 -> 1.1.0)
 * npm run version:major  - увеличить мажорную версию (1.0.0 -> 2.0.0)
 */

import { readFileSync, writeFileSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const rootDir = join(__dirname, '..');

// Получаем тип обновления из аргументов командной строки
const bumpType = process.argv[2] || 'patch';

if (!['major', 'minor', 'patch'].includes(bumpType)) {
  console.error('❌ Неверный тип обновления версии. Используйте: major, minor или patch');
  process.exit(1);
}

/**
 * Увеличивает версию согласно semver
 */
function bumpVersion(version, type) {
  const [major, minor, patch] = version.split('.').map(Number);

  switch (type) {
    case 'major':
      return `${major + 1}.0.0`;
    case 'minor':
      return `${major}.${minor + 1}.0`;
    case 'patch':
      return `${major}.${minor}.${patch + 1}`;
    default:
      throw new Error(`Неизвестный тип обновления: ${type}`);
  }
}

/**
 * Обновляет версию в файле JSON
 */
function updateJsonFile(filePath, newVersion) {
  try {
    const content = readFileSync(filePath, 'utf8');
    const json = JSON.parse(content);

    if (!json.version) {
      console.error(`❌ Файл ${filePath} не содержит поле version`);
      return false;
    }

    const oldVersion = json.version;
    json.version = newVersion;

    // Сохраняем с правильным форматированием (2 пробела)
    writeFileSync(filePath, JSON.stringify(json, null, 2) + '\n', 'utf8');
    console.log(`✅ ${filePath}: ${oldVersion} → ${newVersion}`);

    return true;
  } catch (error) {
    console.error(`❌ Ошибка при обновлении ${filePath}:`, error.message);
    return false;
  }
}

/**
 * Основная функция
 */
function main() {
  console.log(`\n🔄 Обновление версии (${bumpType})...\n`);

  const packageJsonPath = join(rootDir, 'package.json');
  const composerJsonPath = join(rootDir, 'composer.json');

  // Читаем текущую версию из package.json
  let currentVersion;
  try {
    const packageJson = JSON.parse(readFileSync(packageJsonPath, 'utf8'));
    currentVersion = packageJson.version;

    if (!currentVersion) {
      console.error('❌ Версия не найдена в package.json');
      process.exit(1);
    }
  } catch (error) {
    console.error('❌ Ошибка при чтении package.json:', error.message);
    process.exit(1);
  }

  // Вычисляем новую версию
  const newVersion = bumpVersion(currentVersion, bumpType);

  console.log(`Текущая версия: ${currentVersion}`);
  console.log(`Новая версия: ${newVersion}\n`);

  // Обновляем оба файла
  const packageSuccess = updateJsonFile(packageJsonPath, newVersion);
  const composerSuccess = updateJsonFile(composerJsonPath, newVersion);

  if (packageSuccess && composerSuccess) {
    console.log(`\n✨ Версия успешно обновлена до ${newVersion}`);
    console.log('\n💡 Не забудьте закоммитить изменения:');
    console.log(`   git add package.json composer.json`);
    console.log(`   git commit -m "chore: bump version to ${newVersion}"`);
    console.log(`   git tag v${newVersion}`);
    console.log(`   git push && git push --tags\n`);
  } else {
    console.error('\n❌ Произошла ошибка при обновлении версии');
    process.exit(1);
  }
}

main();
