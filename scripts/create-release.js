#!/usr/bin/env node

/**
 * Автоматическое создание релиза на основе Conventional Commits
 *
 * Анализирует коммиты с момента последнего тега и определяет тип обновления версии:
 * - BREAKING CHANGE или feat! -> major (1.0.0 -> 2.0.0)
 * - feat: -> minor (1.0.0 -> 1.1.0)
 * - fix:, docs:, style:, refactor:, perf:, test:, chore: -> patch (1.0.0 -> 1.0.1)
 *
 * Использование:
 * npm run release
 */

import { execSync } from 'child_process';
import { readFileSync, writeFileSync } from 'fs';
import { dirname, join } from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);
const rootDir = join(__dirname, '..');

/**
 * Выполняет команду git и возвращает результат
 */
function git(command) {
  try {
    return execSync(`git ${command}`, { encoding: 'utf8', cwd: rootDir }).trim();
  } catch (error) {
    return '';
  }
}

/**
 * Получает последний тег версии
 */
function getLatestTag() {
  const tags = git('tag --sort=-version:refname');
  if (!tags) return null;

  const tagList = tags.split('\n');
  return tagList[0] || null;
}

/**
 * Получает коммиты с момента последнего тега
 */
function getCommitsSinceTag(tag) {
  const range = tag ? `${tag}..HEAD` : 'HEAD';
  const commits = git(`log ${range} --pretty=format:"%s"`);

  if (!commits) return [];
  return commits.split('\n');
}

/**
 * Определяет тип обновления версии на основе коммитов
 */
function determineBumpType(commits) {
  let hasMajor = false;
  let hasMinor = false;
  let hasPatch = false;

  for (const commit of commits) {
    const lowerCommit = commit.toLowerCase();

    // BREAKING CHANGE или feat! -> major
    if (lowerCommit.includes('breaking change') || /^\w+!:/.test(commit)) {
      hasMajor = true;
      break;
    }

    // feat: -> minor
    if (/^feat(\(.+\))?:/.test(commit)) {
      hasMinor = true;
      continue;
    }

    // fix:, docs:, style:, refactor:, perf:, test:, chore: -> patch
    if (/^(fix|docs|style|refactor|perf|test|chore)(\(.+\))?:/.test(commit)) {
      hasPatch = true;
    }
  }

  if (hasMajor) return 'major';
  if (hasMinor) return 'minor';
  if (hasPatch) return 'patch';

  return null;
}

/**
 * Увеличивает версию
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
 * Обновляет версию в JSON файле
 */
function updateJsonFile(filePath, newVersion) {
  const content = readFileSync(filePath, 'utf8');
  const json = JSON.parse(content);
  json.version = newVersion;
  writeFileSync(filePath, JSON.stringify(json, null, 2) + '\n', 'utf8');
}

/**
 * Основная функция
 */
function main() {
  console.log('\n🔍 Анализ коммитов для создания релиза...\n');

  // Получаем последний тег
  const latestTag = getLatestTag();
  console.log(`Последний тег: ${latestTag || 'не найден'}`);

  // Получаем коммиты с момента последнего тега
  const commits = getCommitsSinceTag(latestTag);

  if (commits.length === 0) {
    console.log('\n⚠️  Нет новых коммитов с момента последнего релиза');
    process.exit(0);
  }

  console.log(`\nНайдено коммитов: ${commits.length}`);
  console.log('\nКоммиты:');
  commits.forEach(commit => console.log(`  - ${commit}`));

  // Определяем тип обновления
  const bumpType = determineBumpType(commits);

  if (!bumpType) {
    console.log('\n⚠️  Не найдено коммитов, требующих обновления версии');
    console.log('Используйте Conventional Commits (feat:, fix:, и т.д.)');
    process.exit(0);
  }

  console.log(`\n📦 Тип обновления: ${bumpType}`);

  // Читаем текущую версию
  const packageJsonPath = join(rootDir, 'package.json');
  const packageJson = JSON.parse(readFileSync(packageJsonPath, 'utf8'));
  const currentVersion = packageJson.version;

  // Вычисляем новую версию
  const newVersion = bumpVersion(currentVersion, bumpType);

  console.log(`\nТекущая версия: ${currentVersion}`);
  console.log(`Новая версия: ${newVersion}\n`);

  // Проверяем, есть ли незакоммиченные изменения
  const status = git('status --porcelain');
  if (status) {
    console.error('❌ Есть незакоммиченные изменения. Закоммитьте их перед созданием релиза.');
    process.exit(1);
  }

  // Обновляем версии в файлах
  updateJsonFile(packageJsonPath, newVersion);
  updateJsonFile(join(rootDir, 'composer.json'), newVersion);

  console.log('✅ Версии обновлены в package.json и composer.json');

  // Создаем коммит с обновлением версии
  git('add package.json composer.json');
  git(`commit -m "chore: bump version to ${newVersion}"`);
  console.log('✅ Создан коммит с обновлением версии');

  // Создаем тег
  git(`tag -a v${newVersion} -m "Release v${newVersion}"`);
  console.log(`✅ Создан тег v${newVersion}`);

  console.log('\n✨ Релиз создан успешно!\n');
  console.log('Для публикации выполните:');
  console.log('  git push && git push --tags\n');
}

main();
