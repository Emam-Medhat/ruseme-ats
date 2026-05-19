import axios from 'axios';

/**
 * Deep semantic job match API.
 *
 * @param {{ jobUrl?: string, jobDescription?: string, jobTitle?: string, resume_id?: number, resume?: object }} payload
 */
export async function matchJob(payload) {
    const { data } = await axios.post(route('api.job.match'), payload, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            Accept: 'application/json',
        },
    });

    return data;
}
