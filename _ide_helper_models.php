<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models\Challenge{
/**
 * @property array<string, array{x: int, y: int, width: int, height: int}>|null $thumbnail_crops
 * @property-read int|null $total_upvotes_count
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $tagline
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $starts_at
 * @property \Illuminate\Support\Carbon|null $ends_at
 * @property bool $is_active
 * @property bool $is_featured
 * @property int|null $organisation_id
 * @property int|null $user_id
 * @property string|null $thumbnail_extension
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Organisation\Organisation|null $organisation
 * @property-read \App\Models\Challenge\ChallengeShowcase|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Showcase\Showcase> $showcases
 * @property-read int|null $showcases_count
 * @property-read array|null $thumbnail_rect_strings
 * @property-read string|null $thumbnail_url
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\Challenge\ChallengeFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereEndsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereOrganisationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereStartsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereThumbnailCrops($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereThumbnailExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge withTotalUpvotesCount()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperChallenge {}
}

namespace App\Models\Challenge{
/**
 * @property int $challenge_id
 * @property int $showcase_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Challenge\Challenge $challenge
 * @property-read \App\Models\Showcase\Showcase $showcase
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChallengeShowcase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChallengeShowcase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChallengeShowcase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChallengeShowcase whereChallengeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChallengeShowcase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChallengeShowcase whereShowcaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ChallengeShowcase whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperChallengeShowcase {}
}

namespace App\Models\Course{
/**
 * @property array<string, array{x: int, y: int, width: int, height: int}>|null $thumbnail_crops
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $tagline
 * @property string|null $description
 * @property string|null $learning_objectives
 * @property int $order
 * @property \App\Enums\ExperienceLevel|null $experience_level
 * @property int|null $duration_seconds
 * @property int $started_count
 * @property int $completed_count
 * @property bool $allow_preview
 * @property bool $is_featured
 * @property \Illuminate\Support\Carbon|null $publish_date
 * @property string|null $thumbnail_filename
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Course\Lesson> $lessons
 * @property-read int|null $lessons_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Course\Lesson> $publishedLessons
 * @property-read int|null $published_lessons_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @property-read array|null $thumbnail_rect_strings
 * @property-read string|null $thumbnail_url
 * @property-read \App\Models\Course\CourseUser|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Course\Lesson> $visibleLessons
 * @property-read int|null $visible_lessons_count
 * @method static \Database\Factories\Course\CourseFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course visible()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereAllowPreview($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCompletedCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereDurationSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereExperienceLevel($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereLearningObjectives($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course wherePublishDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereStartedCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereThumbnailCrops($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereThumbnailFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Course whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCourse {}
}

namespace App\Models\Course{
/**
 * @property int $id
 * @property int $course_id
 * @property int $user_id
 * @property \Illuminate\Support\Carbon|null $viewed_at
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\Course\CourseUserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseUser whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseUser whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseUser whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseUser whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CourseUser whereViewedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperCourseUser {}
}

namespace App\Models\Course{
/**
 * @property array<string, array{x: int, y: int, width: int, height: int}>|null $thumbnail_crops
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string|null $tagline
 * @property string|null $description
 * @property string|null $learning_objectives
 * @property string|null $copy
 * @property string|null $asset_id
 * @property string|null $playback_id
 * @property \App\Enums\VideoHost|null $host
 * @property int|null $duration_seconds
 * @property bool $gated
 * @property string|null $thumbnail_filename
 * @property int $order
 * @property bool $allow_preview
 * @property \Illuminate\Support\Carbon|null $publish_date
 * @property int $course_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Course\Course $course
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $instructors
 * @property-read int|null $instructors_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Tag> $tags
 * @property-read int|null $tags_count
 * @property-read array|null $thumbnail_rect_strings
 * @property-read string|null $thumbnail_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Course\LessonTranscriptLine> $transcriptLines
 * @property-read int|null $transcript_lines_count
 * @property-read string|null $transcript_txt
 * @property-read string|null $transcript_vtt
 * @property-read \App\Models\Course\LessonUser|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\Course\LessonFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson visible()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereAllowPreview($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereAssetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereCopy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereCourseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereDurationSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereGated($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereLearningObjectives($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson wherePlaybackId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson wherePublishDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereThumbnailCrops($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereThumbnailFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Lesson whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperLesson {}
}

namespace App\Models\Course{
/**
 * @property int $id
 * @property int $lesson_id
 * @property numeric $start_seconds
 * @property numeric $end_seconds
 * @property string $text
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Course\Lesson $lesson
 * @method static \Database\Factories\Course\LessonTranscriptLineFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonTranscriptLine newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonTranscriptLine newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonTranscriptLine query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonTranscriptLine whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonTranscriptLine whereEndSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonTranscriptLine whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonTranscriptLine whereLessonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonTranscriptLine whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonTranscriptLine whereStartSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonTranscriptLine whereText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonTranscriptLine whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperLessonTranscriptLine {}
}

namespace App\Models\Course{
/**
 * @property int $id
 * @property int $user_id
 * @property int $lesson_id
 * @property \Illuminate\Support\Carbon|null $viewed_at
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property int|null $playback_time_seconds
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Database\Factories\Course\LessonUserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonUser newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonUser newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonUser query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonUser whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonUser whereLessonId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonUser wherePlaybackTimeSeconds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonUser whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonUser whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonUser whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LessonUser whereViewedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperLessonUser {}
}

namespace App\Models\Organisation{
/**
 * @property array<string, array{x: int, y: int, width: int, height: int}>|null $thumbnail_crops
 * @property int $id
 * @property string $name
 * @property string $tagline
 * @property string $about
 * @property string|null $thumbnail_extension
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Challenge\Challenge> $challenges
 * @property-read int|null $challenges_count
 * @property-read array|null $thumbnail_rect_strings
 * @property-read string|null $thumbnail_url
 * @method static \Database\Factories\Organisation\OrganisationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereAbout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereThumbnailCrops($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereThumbnailExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Organisation whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperOrganisation {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Showcase\Showcase> $showcases
 * @property-read int|null $showcases_count
 * @method static \Database\Factories\PracticeAreaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeArea newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeArea newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeArea query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeArea whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeArea whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeArea whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeArea whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PracticeArea whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPracticeArea {}
}

namespace App\Models{
/**
 * @property array{x: int, y: int, width: int, height: int}|null $thumbnail_crop
 * @property int $id
 * @property string $title
 * @property string $publication_name
 * @property \Illuminate\Support\Carbon $publication_date
 * @property string $url
 * @property string|null $excerpt
 * @property string|null $thumbnail_extension
 * @property bool $is_published
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $thumbnail_rect_string
 * @property-read string|null $thumbnail_url
 * @method static \Database\Factories\PressCoverageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage whereExcerpt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage wherePublicationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage wherePublicationName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage whereThumbnailCrop($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage whereThumbnailExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PressCoverage whereUrl($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperPressCoverage {}
}

namespace App\Models\Showcase{
/**
 * @property array{x: int, y: int, width: int, height: int}|null $thumbnail_crop
 * @property int $id
 * @property int|null $user_id
 * @property string $title
 * @property string $slug
 * @property string $tagline
 * @property string $description
 * @property string|null $key_features
 * @property string|null $help_needed
 * @property string|null $url
 * @property string|null $video_url
 * @property \App\Enums\SourceStatus $source_status
 * @property string|null $source_url
 * @property string|null $thumbnail_extension
 * @property \App\Enums\ShowcaseStatus $status
 * @property \Illuminate\Support\Carbon|null $submitted_date
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $approval_celebrated_at
 * @property int|null $approved_by
 * @property string|null $rejection_reason
 * @property int $view_count
 * @property bool $is_featured
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \App\Models\Challenge\ChallengeShowcase|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Challenge\Challenge> $challenges
 * @property-read int|null $challenges_count
 * @property-read \App\Models\Showcase\ShowcaseDraft|null $draft
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Showcase\ShowcaseImage> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PracticeArea> $practiceAreas
 * @property-read int|null $practice_areas_count
 * @property-read string|null $thumbnail_rect_string
 * @property-read string|null $thumbnail_url
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $upvoters
 * @property-read int|null $upvoters_count
 * @property-read \App\Models\User|null $user
 * @property-read string|null $youtube_id
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase approved()
 * @method static \Database\Factories\Showcase\ShowcaseFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase featured()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase pending()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase publiclyVisible()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereApprovalCelebratedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereHelpNeeded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereKeyFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereSourceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereSourceUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereSubmittedDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereThumbnailCrop($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereThumbnailExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereVideoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase whereViewCount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Showcase withoutTrashed()
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperShowcase {}
}

namespace App\Models\Showcase{
/**
 * @property array{x: int, y: int, width: int, height: int}|null $thumbnail_crop
 * @property int $id
 * @property int $showcase_id
 * @property string $title
 * @property string $tagline
 * @property string $description
 * @property string|null $key_features
 * @property string|null $help_needed
 * @property string|null $url
 * @property string|null $video_url
 * @property \App\Enums\SourceStatus $source_status
 * @property string|null $source_url
 * @property string|null $thumbnail_extension
 * @property \App\Enums\ShowcaseDraftStatus $status
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property string|null $rejection_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Showcase\ShowcaseDraftImage> $images
 * @property-read int|null $images_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PracticeArea> $practiceAreas
 * @property-read int|null $practice_areas_count
 * @property-read \App\Models\Showcase\Showcase $showcase
 * @property-read string|null $thumbnail_rect_string
 * @property-read string|null $thumbnail_url
 * @method static \Database\Factories\Showcase\ShowcaseDraftFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereHelpNeeded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereKeyFeatures($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereRejectionReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereShowcaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereSourceStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereSourceUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereTagline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereThumbnailCrop($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereThumbnailExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraft whereVideoUrl($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperShowcaseDraft {}
}

namespace App\Models\Showcase{
/**
 * @property int $id
 * @property int $showcase_draft_id
 * @property int|null $original_image_id
 * @property string $action
 * @property string|null $path
 * @property string|null $filename
 * @property string|null $alt_text
 * @property int $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Showcase\ShowcaseImage|null $originalImage
 * @property-read \App\Models\Showcase\ShowcaseDraft $showcaseDraft
 * @property-read string|null $url
 * @method static \Database\Factories\Showcase\ShowcaseDraftImageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraftImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraftImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraftImage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraftImage whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraftImage whereAltText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraftImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraftImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraftImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraftImage whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraftImage whereOriginalImageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraftImage wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraftImage whereShowcaseDraftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseDraftImage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperShowcaseDraftImage {}
}

namespace App\Models\Showcase{
/**
 * @property int $id
 * @property int $showcase_id
 * @property string $path
 * @property string $filename
 * @property int $order
 * @property string|null $alt_text
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Showcase\Showcase $showcase
 * @property-read string $url
 * @method static \Database\Factories\Showcase\ShowcaseImageFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseImage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseImage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseImage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseImage whereAltText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseImage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseImage whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseImage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseImage whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseImage wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseImage whereShowcaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseImage whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperShowcaseImage {}
}

namespace App\Models\Showcase{
/**
 * @property int $id
 * @property int $user_id
 * @property int $showcase_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Showcase\Showcase $showcase
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseUpvote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseUpvote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseUpvote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseUpvote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseUpvote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseUpvote whereShowcaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseUpvote whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ShowcaseUpvote whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperShowcaseUpvote {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property \App\Enums\TagType $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Course\Course> $courses
 * @property-read int|null $courses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Course\Lesson> $lessons
 * @property-read int|null $lessons_count
 * @method static \Database\Factories\TagFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tag whereUpdatedAt($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTag {}
}

namespace App\Models{
/**
 * @property array{x: int, y: int, width: int, height: int}|null $avatar_crop
 * @property int $id
 * @property int|null $user_id
 * @property string|null $name
 * @property string|null $job_title
 * @property string|null $organisation
 * @property string $content
 * @property string|null $avatar_path
 * @property bool $is_published
 * @property int $display_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read string|null $avatar
 * @property-read string|null $avatar_rect_string
 * @property-read string|null $display_job_title
 * @property-read string $display_name
 * @property-read string|null $display_organisation
 * @property-read \App\Models\User|null $user
 * @method static \Database\Factories\TestimonialFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial published()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereAvatarCrop($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereAvatarPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereDisplayOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereJobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereOrganisation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Testimonial whereUserId($value)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperTestimonial {}
}

namespace App\Models{
/**
 * @property \Illuminate\Support\Carbon|null $marketing_opt_out_at
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $handle
 * @property string|null $organisation
 * @property string|null $job_title
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string|null $password
 * @property bool $is_admin
 * @property string|null $avatar_path
 * @property string|null $linkedin_url
 * @property string|null $bio
 * @property string|null $linkedin_id
 * @property string|null $linkedin_token
 * @property \Illuminate\Support\Carbon|null $blocked_from_submissions_at
 * @property \App\Enums\TeamType|null $team_type
 * @property string|null $team_role
 * @property int|null $team_order
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property \Illuminate\Support\Carbon|null $two_factor_confirmed_at
 * @property string|null $external_subscriber_uuid
 * @property-read string|null $avatar
 * @property-read \App\Models\Course\LessonUser|\App\Models\Course\CourseUser|null $pivot
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Course\Course> $courses
 * @property-read int|null $courses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Challenge\Challenge> $hostedChallenges
 * @property-read int|null $hosted_challenges_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Course\Lesson> $lessons
 * @property-read int|null $lessons_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Showcase\Showcase> $showcases
 * @property-read int|null $showcases_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Showcase\Showcase> $upvotedShowcases
 * @property-read int|null $upvoted_showcases_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User collaborators()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User coreTeam()
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User teamMembers()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatarPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBio($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBlockedFromSubmissionsAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereExternalSubscriberUuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereHandle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereJobTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLinkedinId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLinkedinToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereLinkedinUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereMarketingOptOutAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereOrganisation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTeamOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTeamRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTeamType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorConfirmedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorRecoveryCodes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereTwoFactorSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 * @mixin \Eloquent
 */
	#[\AllowDynamicProperties]
	class IdeHelperUser {}
}

